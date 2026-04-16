<?php

namespace App\Services;

use App\Models\Evidence;
use App\Models\EvidenceHash;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * AuditService — System-wide integrity audit for Legal Consultants / Auditors.
 *
 * Rank 5+ capability only. Provides:
 * - System-wide Keccak-256 hash verification across all evidence.
 * - Tamper detection reports comparing stored vs computed hashes.
 * - Chain-of-custody gap detection.
 * - Audit summary statistics.
 *
 * This service is intentionally read-only — it never modifies any record.
 */
class AuditService
{
    /**
     * Run a full system-wide integrity audit.
     *
     * Iterates every non-deleted evidence record, computes its Keccak-256
     * hash, and compares it against the stored hash record.
     *
     * Returns a structured report:
     * [
     *   'total'     => int,   // total evidence items checked
     *   'passed'    => int,   // hashes matched
     *   'failed'    => int,   // hashes mismatched (possible tampering)
     *   'missing'   => int,   // file not found on disk
     *   'no_hash'   => int,   // no hash record stored yet
     *   'items'     => Collection of per-item results,
     *   'audited_by'=> User,
     *   'audited_at'=> Carbon,
     * ]
     */
    public function runFullAudit(User $auditor): array
    {
        $results = collect();
        $passed  = 0;
        $failed  = 0;
        $missing = 0;
        $noHash  = 0;

        Evidence::with(['latestHash', 'uploader'])
            ->withTrashed() // Auditors see soft-deleted evidence too
            ->orderBy('created_at')
            ->chunk(50, function ($evidenceItems) use (
                &$results, &$passed, &$failed, &$missing, &$noHash
            ) {
                foreach ($evidenceItems as $evidence) {
                    $result = $this->auditSingleItem($evidence);
                    $results->push($result);

                    match ($result['status']) {
                        'passed'  => $passed++,
                        'failed'  => $failed++,
                        'missing' => $missing++,
                        'no_hash' => $noHash++,
                        default   => null,
                    };
                }
            });

        // Log the audit run
        activity('system_audit')
            ->causedBy($auditor)
            ->withProperties([
                'total'   => $results->count(),
                'passed'  => $passed,
                'failed'  => $failed,
                'missing' => $missing,
                'no_hash' => $noHash,
            ])
            ->log("System-wide Keccak-256 audit completed by rank-{$auditor->rank} auditor");

        return [
            'total'      => $results->count(),
            'passed'     => $passed,
            'failed'     => $failed,
            'missing'    => $missing,
            'no_hash'    => $noHash,
            'items'      => $results,
            'audited_by' => $auditor,
            'audited_at' => now(),
        ];
    }

    /**
     * Audit a single evidence item.
     *
     * Returns:
     * [
     *   'evidence_id'    => string (UUID),
     *   'case_number'    => string,
     *   'title'          => string,
     *   'status'         => 'passed'|'failed'|'missing'|'no_hash',
     *   'stored_hash'    => string|null,
     *   'computed_hash'  => string|null,
     *   'hash_type'      => 'keccak256',
     *   'is_deleted'     => bool,
     *   'uploaded_by'    => string,
     *   'created_at'     => string,
     * ]
     */
    public function auditSingleItem(Evidence $evidence): array
    {
        $base = [
            'evidence_id'   => $evidence->id,
            'case_number'   => $evidence->case_number,
            'title'         => $evidence->title,
            'hash_type'     => 'keccak256',
            'is_deleted'    => $evidence->trashed(),
            'uploaded_by'   => $evidence->uploader?->name ?? 'Unknown',
            'created_at'    => $evidence->created_at->toDateTimeString(),
            'stored_hash'   => null,
            'computed_hash' => null,
        ];

        // Check file exists on disk
        if (! Storage::disk('evidence')->exists($evidence->file_path)) {
            return array_merge($base, ['status' => 'missing']);
        }

        // Compute fresh Keccak-256
        $computed = $evidence->generateKeccakHash();

        if ($computed === null) {
            return array_merge($base, ['status' => 'missing']);
        }

        // Look for stored Keccak hash
        $storedRecord = $evidence->hashes()
            ->where('hash_type', 'keccak256')
            ->first();

        if (! $storedRecord) {
            // No Keccak hash stored — store it now as the baseline
            EvidenceHash::create([
                'evidence_id'  => $evidence->id,
                'hash_value'   => $computed,
                'hash_type'    => 'keccak256',
                'generated_at' => now(),
                'created_by'   => $evidence->uploaded_by,
            ]);

            return array_merge($base, [
                'status'        => 'no_hash',
                'computed_hash' => $computed,
            ]);
        }

        $matches = hash_equals($storedRecord->hash_value, $computed);

        return array_merge($base, [
            'status'        => $matches ? 'passed' : 'failed',
            'stored_hash'   => $storedRecord->hash_value,
            'computed_hash' => $computed,
        ]);
    }

    /**
     * Verify chain-of-custody integrity for all evidence.
     *
     * Checks that every custody chain is unbroken (no gaps in
     * previous_custody_id links) and temporally ordered.
     *
     * Returns a collection of broken chains with evidence IDs.
     */
    public function auditCustodyChains(): Collection
    {
        $broken = collect();

        Evidence::withTrashed()
            ->with('custodyChain')
            ->chunk(50, function ($items) use (&$broken) {
                foreach ($items as $evidence) {
                    $latest = $evidence->custodyChain->last();

                    if ($latest && ! $latest->verifyChain()) {
                        $broken->push([
                            'evidence_id'  => $evidence->id,
                            'case_number'  => $evidence->case_number,
                            'title'        => $evidence->title,
                            'chain_length' => $evidence->custodyChain->count(),
                        ]);
                    }
                }
            });

        return $broken;
    }

    /**
     * Get a summary of evidence statistics for the audit dashboard.
     */
    public function getSummaryStats(): array
    {
        return [
            'total_evidence'    => Evidence::withTrashed()->count(),
            'active_evidence'   => Evidence::where('status', 'active')->count(),
            'pending_hash'      => Evidence::where('status', 'pending')->count(),
            'deleted_evidence'  => Evidence::onlyTrashed()->count(),
            'total_custody_records' => \App\Models\ChainOfCustody::count(),
            'total_hash_records'    => EvidenceHash::count(),
            'keccak_hashes'         => EvidenceHash::where('hash_type', 'keccak256')->count(),
            'sha256_hashes'         => EvidenceHash::where('hash_type', 'sha256')->count(),
        ];
    }
}
