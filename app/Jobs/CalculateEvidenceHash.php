<?php

namespace App\Jobs;

use App\Models\Evidence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * CalculateEvidenceHash
 *
 * Queued job that computes the SHA-256 hash of an uploaded evidence file
 * and stores it in the evidence_hashes table.
 *
 * Why a job?
 * - Large files (up to 2 GB) can take several seconds to hash.
 * - Offloading to a queue keeps the HTTP response fast.
 * - The Evidence model's boot() already calls generateHash() synchronously
 *   on creation, but for large files we dispatch this job INSTEAD to avoid
 *   blocking the request. The controller skips the synchronous hash by
 *   passing 'skip_auto_hash' => true (handled in the model boot).
 *
 * Note: The Evidence model's boot() creates the initial ChainOfCustody
 * record automatically — this job only handles the hash calculation.
 */
class CalculateEvidenceHash implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     * Hashing is idempotent — safe to retry on transient failures.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying after a failure.
     */
    public int $backoff = 10;

    /**
     * Maximum seconds the job may run before timing out.
     * 2 GB file at ~500 MB/s I/O = ~4 seconds; 120 s is generous.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly string $evidenceId
    ) {}

    public function handle(): void
    {
        $evidence = Evidence::find($this->evidenceId);

        if (! $evidence) {
            Log::warning("CalculateEvidenceHash: evidence {$this->evidenceId} not found — skipping.");
            return;
        }

        // The Evidence model's boot() already called generateHash() synchronously
        // on creation. This job ensures:
        //   1. The hash exists (re-generates if the sync call failed for large files).
        //   2. Status is updated to 'active' once hashing is confirmed complete.
        $hash = $evidence->latestHash ?? $evidence->generateHash();

        if (! $hash) {
            Log::error("CalculateEvidenceHash: failed to hash evidence {$this->evidenceId}. File may be missing.");
            $this->fail(new \RuntimeException("Could not hash evidence file: {$this->evidenceId}"));
            return;
        }

        // Transition from 'pending' → 'active' now that the hash is confirmed
        if ($evidence->status === 'pending') {
            $evidence->update(['status' => 'active']);
        }

        Log::info("CalculateEvidenceHash: confirmed hash for evidence {$this->evidenceId} → {$hash->hash_value}");
    }

    /**
     * Handle a job failure — log it for forensic audit.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("CalculateEvidenceHash job failed for evidence {$this->evidenceId}: {$exception->getMessage()}");
    }
}
