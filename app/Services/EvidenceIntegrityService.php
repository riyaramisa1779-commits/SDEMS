<?php

namespace App\Services;

use App\Models\Evidence;
use App\Models\EvidenceHash;
use Illuminate\Support\Facades\Storage;

/**
 * EvidenceIntegrityService
 *
 * Responsible for SHA-256 integrity verification of evidence files.
 * Compares the current file hash against the stored hash in evidence_hashes.
 *
 * Security principles:
 * - All file access goes through Storage::disk('evidence') — never raw paths.
 * - hash_equals() is used for all comparisons (timing-safe).
 * - This service is read-only by default; re-hashing requires explicit intent.
 * - Every verification attempt is logged via Spatie Activitylog.
 */
class EvidenceIntegrityService
{
    // ── Status constants ──────────────────────────────────────────────────────

    public const STATUS_VERIFIED = 'Verified';
    public const STATUS_TAMPERED = 'Tampered';
    public const STATUS_PENDING  = 'Pending';
    public const STATUS_MISSING  = 'Missing';

    // ── Single Evidence Verification ──────────────────────────────────────────

    /**
     * Verify the integrity of a single evidence file.
     *
     * Returns a structured result array:
     * [
     *   'status'        => 'Verified'|'Tampered'|'Pending'|'Missing',
     *   'passed'        => bool,
     *   'stored_hash'   => string|null,
     *   'computed_hash' => string|null,
     *   'hash_type'     => 'sha256',
     *   'verified_at'   => Carbon,
     *   'message'       => string,
     * ]
     */
    public function verify(Evidence $evidence): array
    {
        $disk = Storage::disk('evidence');

        // ── File existence check ──────────────────────────────────────────────
        if (! $disk->exists($evidence->file_path)) {
            return $this->buildResult(
                status:       self::STATUS_MISSING,
                passed:       false,
                storedHash:   null,
                computedHash: null,
                message:      'Evidence file not found on storage disk.',
            );
        }

        // ── No stored hash yet ────────────────────────────────────────────────
        $storedRecord = $evidence->hashes()
            ->where('hash_type', 'sha256')
            ->first();

        if (! $storedRecord) {
            return $this->buildResult(
                status:       self::STATUS_PENDING,
                passed:       false,
                storedHash:   null,
                computedHash: null,
                message:      'No SHA-256 hash record found. Hash calculation may still be pending.',
            );
        }

        // ── Compute current hash ──────────────────────────────────────────────
        $computed = $this->computeHash($evidence->file_path);

        if ($computed === null) {
            return $this->buildResult(
                status:       self::STATUS_MISSING,
                passed:       false,
                storedHash:   $storedRecord->hash_value,
                computedHash: null,
                message:      'Failed to read evidence file for hash computation.',
            );
        }

        // ── Timing-safe comparison ────────────────────────────────────────────
        $matches = hash_equals($storedRecord->hash_value, $computed);

        return $this->buildResult(
            status:       $matches ? self::STATUS_VERIFIED : self::STATUS_TAMPERED,
            passed:       $matches,
            storedHash:   $storedRecord->hash_value,
            computedHash: $computed,
            message:      $matches
                ? 'File integrity confirmed. SHA-256 hash matches stored record.'
                : 'INTEGRITY VIOLATION: Current file hash does not match stored hash. Possible tampering detected.',
        );
    }

    /**
     * Verify and log the result to the activity log.
     *
     * Use this for all user-triggered verifications so every attempt
     * is recorded with the user's rank and the outcome.
     */
    public function verifyAndLog(Evidence $evidence, \App\Models\User $verifier): array
    {
        $result = $this->verify($evidence);

        // Log every verification attempt — success or failure
        $logName = $result['passed'] ? 'integrity_verified' : 'integrity_alert';

        activity($logName)
            ->causedBy($verifier)
            ->performedOn($evidence)
            ->withProperties([
                'case_number'   => $evidence->case_number,
                'status'        => $result['status'],
                'stored_hash'   => $result['stored_hash']
                    ? substr($result['stored_hash'], 0, 16) . '...'
                    : null,
                'computed_hash' => $result['computed_hash']
                    ? substr($result['computed_hash'], 0, 16) . '...'
                    : null,
                'user_rank'     => $verifier->rank,
                'ip'            => request()->ip(),
            ])
            ->log($result['passed']
                ? "Integrity verified for evidence [{$evidence->case_number}]"
                : "INTEGRITY ALERT: Tampering detected on evidence [{$evidence->case_number}]"
            );

        return $result;
    }

    /**
     * Re-generate the SHA-256 hash for an evidence file and store it.
     *
     * This creates a NEW hash record (append-only) — it does not overwrite
     * the existing record. Used by rank 8+ administrators only.
     *
     * Returns the new EvidenceHash record, or null on failure.
     */
    public function regenerateHash(Evidence $evidence, \App\Models\User $operator): ?EvidenceHash
    {
        $computed = $this->computeHash($evidence->file_path);

        if ($computed === null) {
            return null;
        }

        $hashRecord = EvidenceHash::create([
            'evidence_id'  => $evidence->id,
            'hash_value'   => $computed,
            'hash_type'    => 'sha256',
            'generated_at' => now(),
            'created_by'   => $operator->id,
        ]);

        activity('integrity_rehash')
            ->causedBy($operator)
            ->performedOn($evidence)
            ->withProperties([
                'case_number' => $evidence->case_number,
                'new_hash'    => substr($computed, 0, 16) . '...',
                'user_rank'   => $operator->rank,
                'ip'          => request()->ip(),
            ])
            ->log("Hash re-generated for evidence [{$evidence->case_number}] by rank-{$operator->rank} operator");

        return $hashRecord;
    }

    // ── Bulk Verification ─────────────────────────────────────────────────────

    /**
     * Run a bulk integrity check across all (non-deleted) evidence.
     *
     * Returns summary statistics and per-item results.
     * Processes in chunks to avoid memory exhaustion on large datasets.
     */
    public function bulkVerify(\App\Models\User $verifier): array
    {
        $results  = collect();
        $verified = 0;
        $tampered = 0;
        $pending  = 0;
        $missing  = 0;

        Evidence::with(['latestHash', 'uploader'])
            ->orderBy('created_at')
            ->chunk(50, function ($items) use (
                &$results, &$verified, &$tampered, &$pending, &$missing
            ) {
                foreach ($items as $evidence) {
                    $result = $this->verify($evidence);
                    $results->push(array_merge($result, [
                        'evidence_id'   => $evidence->id,
                        'case_number'   => $evidence->case_number,
                        'title'         => $evidence->title,
                        'original_name' => $evidence->original_name,
                        'uploaded_by'   => $evidence->uploader?->name ?? 'Unknown',
                    ]));

                    match ($result['status']) {
                        self::STATUS_VERIFIED => $verified++,
                        self::STATUS_TAMPERED => $tampered++,
                        self::STATUS_PENDING  => $pending++,
                        self::STATUS_MISSING  => $missing++,
                        default               => null,
                    };
                }
            });

        // Log the bulk run
        activity('integrity_bulk_check')
            ->causedBy($verifier)
            ->withProperties([
                'total'    => $results->count(),
                'verified' => $verified,
                'tampered' => $tampered,
                'pending'  => $pending,
                'missing'  => $missing,
                'user_rank' => $verifier->rank,
            ])
            ->log("Bulk integrity check completed by rank-{$verifier->rank} user");

        return [
            'total'    => $results->count(),
            'verified' => $verified,
            'tampered' => $tampered,
            'pending'  => $pending,
            'missing'  => $missing,
            'items'    => $results,
            'run_by'   => $verifier,
            'run_at'   => now(),
        ];
    }

    // ── Dashboard Stats ───────────────────────────────────────────────────────

    /**
     * Get integrity status counts for the dashboard summary cards.
     * Uses a lightweight query — does NOT read any files.
     */
    public function getDashboardStats(): array
    {
        $total    = Evidence::count();
        $withHash = Evidence::whereHas('hashes', fn ($q) => $q->where('hash_type', 'sha256'))->count();
        $pending  = $total - $withHash;

        return [
            'total'   => $total,
            'pending' => $pending,
            // Verified/Tampered counts require actual file reads — computed on demand
        ];
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Compute the SHA-256 hash of a file on the evidence disk.
     * Streams the file to avoid loading large files into memory.
     * Returns null if the file cannot be read.
     */
    private function computeHash(string $filePath): ?string
    {
        $disk = Storage::disk('evidence');

        if (! $disk->exists($filePath)) {
            return null;
        }

        try {
            $stream  = $disk->readStream($filePath);
            $context = hash_init('sha256');
            hash_update_stream($context, $stream);
            $digest  = hash_final($context);

            if (is_resource($stream)) {
                fclose($stream);
            }

            return $digest;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Build a standardised result array.
     */
    private function buildResult(
        string  $status,
        bool    $passed,
        ?string $storedHash,
        ?string $computedHash,
        string  $message,
    ): array {
        return [
            'status'        => $status,
            'passed'        => $passed,
            'stored_hash'   => $storedHash,
            'computed_hash' => $computedHash,
            'hash_type'     => 'sha256',
            'verified_at'   => now(),
            'message'       => $message,
        ];
    }
}
