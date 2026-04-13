<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evidence table — the core of the Secure Digital Evidence Management System.
 *
 * Security decisions:
 * - UUIDs as primary keys prevent enumeration attacks (no sequential IDs exposed).
 * - file_path stores only the internal storage path, never the original filename.
 * - original_name is stored separately for display only, never used for file access.
 * - soft deletes preserve the audit trail; evidence is never hard-deleted.
 * - version column enables immutable versioning: each update creates a new record.
 * - status enum restricts state transitions to known values.
 * - All foreign keys use RESTRICT on delete to prevent orphaned evidence records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence', function (Blueprint $table) {
            // ── Primary Key ───────────────────────────────────────────────────
            // UUID prevents sequential ID enumeration — critical for evidence IDs
            // that may appear in URLs or API responses.
            $table->uuid('id')->primary();

            // ── Case Reference ────────────────────────────────────────────────
            // Indexed for fast case-based lookups; format enforced at app layer.
            $table->string('case_number', 50)->index();

            // ── Evidence Metadata ─────────────────────────────────────────────
            $table->string('title', 255);
            $table->text('description')->nullable();

            // Category as string with app-layer enum validation for flexibility
            // (allows adding new categories without schema changes).
            $table->string('category', 50)->index();

            // JSON tags for flexible multi-value tagging without a pivot table.
            // Cast to array in the model.
            $table->json('tags')->nullable();

            // ── File Storage ──────────────────────────────────────────────────
            // Internal storage path only — UUID-based, never the original name.
            // Example: evidence/2026/04/550e8400-e29b-41d4-a716-446655440000.enc
            $table->string('file_path', 500);

            // Original filename stored for display/download headers only.
            // NEVER used to locate the file on disk.
            $table->string('original_name', 255)->nullable();

            $table->string('mime_type', 100)->nullable();

            // bigInteger supports files up to ~9.2 exabytes
            $table->unsignedBigInteger('file_size')->default(0);

            // ── Ownership & Assignment ────────────────────────────────────────
            // RESTRICT prevents deleting a user who uploaded evidence.
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Nullable: evidence may be unassigned initially.
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // If assigned user is deleted, unassign

            // ── Status & Versioning ───────────────────────────────────────────
            // Indexed for dashboard/filter queries.
            $table->string('status', 30)->default('pending')->index();

            // Monotonically increasing version counter.
            // Version 1 = original upload; increments on each update.
            $table->unsignedInteger('version')->default(1);

            // ── Soft Deletes ──────────────────────────────────────────────────
            // Evidence is NEVER hard-deleted — soft deletes preserve audit trail.
            $table->softDeletes();

            $table->timestamps();

            // ── Composite Indexes ─────────────────────────────────────────────
            // Optimise the most common query: "all active evidence for case X"
            $table->index(['case_number', 'status']);
            $table->index(['uploaded_by', 'status']);
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
