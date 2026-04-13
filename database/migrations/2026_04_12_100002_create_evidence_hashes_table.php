<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evidence hashes table — cryptographic integrity records.
 *
 * Security decisions:
 * - Separate table ensures hash records cannot be silently overwritten
 *   when the evidence record is updated (immutable append-only design).
 * - Multiple hashes per evidence item support re-hashing after transfers
 *   and hash-type upgrades (e.g., SHA-256 → SHA-3 in future).
 * - hash_value is exactly 64 chars for SHA-256 hex digest.
 * - created_by links to the user who generated the hash (non-nullable)
 *   for full accountability.
 * - No soft deletes: hash records must never be deleted.
 * - No updated_at: hash records are immutable once created.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_hashes', function (Blueprint $table) {
            $table->id();

            // RESTRICT: cannot delete evidence while hashes exist
            $table->foreignUuid('evidence_id')
                  ->constrained('evidence')
                  ->restrictOnDelete();

            // SHA-256 hex digest is always exactly 64 characters
            $table->char('hash_value', 64);

            // Supports future hash algorithm upgrades
            $table->string('hash_type', 20)->default('sha256');

            // When the hash was computed (may differ from created_at)
            $table->timestamp('generated_at');

            // Who generated this hash (officer, system, etc.)
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            // created_at only — hash records are immutable
            $table->timestamp('created_at')->useCurrent();

            // ── Indexes ───────────────────────────────────────────────────────
            // Fast lookup of latest hash for an evidence item
            $table->index(['evidence_id', 'created_at']);

            // Detect duplicate/colliding hashes across all evidence
            $table->index('hash_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_hashes');
    }
};
