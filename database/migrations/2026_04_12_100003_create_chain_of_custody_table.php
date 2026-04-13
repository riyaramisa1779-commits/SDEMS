<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chain of custody table — tamper-evident custody transfer log.
 *
 * Security decisions:
 * - previous_custody_id creates a linked-list chain: each record references
 *   the prior one, making it computationally difficult to insert or remove
 *   records without breaking the chain.
 * - signature field stores a digital signature or acknowledgement token
 *   for court-admissible handoff verification.
 * - No soft deletes and no updated_at: custody records are immutable.
 * - RESTRICT on all foreign keys: cannot delete users or evidence while
 *   custody records reference them.
 * - timestamp is stored explicitly (not relying on created_at) so the
 *   exact moment of custody transfer is recorded independently.
 * - location field captures physical location of transfer for forensic use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chain_of_custody', function (Blueprint $table) {
            $table->id();

            // RESTRICT: evidence cannot be deleted while custody records exist
            $table->foreignUuid('evidence_id')
                  ->constrained('evidence')
                  ->restrictOnDelete();

            // Who transferred custody FROM (null = system/initial upload)
            $table->foreignId('from_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->restrictOnDelete();

            // Who received custody TO
            $table->foreignId('to_user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Controlled vocabulary for custody actions
            $table->enum('action', [
                'upload',    // Initial evidence upload
                'transfer',  // Custody transferred between officers
                'checkout',  // Checked out for analysis/court
                'checkin',   // Returned after checkout
                'review',    // Reviewed without transfer
                'seal',      // Sealed for long-term storage
                'unseal',    // Unsealed for access
                'export',    // Exported/copied for external use
                'delete',    // Soft-deleted (marked for deletion)
                'restore',   // Restored from soft-delete
            ])->index();

            // Free-text notes for context (reason for transfer, court case ref, etc.)
            $table->text('notes')->nullable();

            // Physical or logical location at time of action
            $table->string('location', 255)->nullable();

            // Exact timestamp of the custody event (separate from created_at)
            $table->timestamp('timestamp');

            // Digital signature / acknowledgement token for court admissibility.
            // In production: store a signed hash of (evidence_id + action + timestamp + to_user_id).
            $table->text('signature')->nullable();

            // ── Chain Linking ─────────────────────────────────────────────────
            // Self-referential FK creates a verifiable linked list.
            // NULL = first record in the chain (initial upload).
            $table->foreignId('previous_custody_id')
                  ->nullable()
                  ->constrained('chain_of_custody')
                  ->restrictOnDelete();

            // created_at only — records are immutable
            $table->timestamp('created_at')->useCurrent();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['evidence_id', 'timestamp']);
            $table->index(['to_user_id', 'action']);
            $table->index('from_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chain_of_custody');
    }
};
