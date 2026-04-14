<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix activity_log subject_id and causer_id columns.
 *
 * Problem: The original migration used nullableMorphs() which creates
 * subject_id as unsignedBigInteger. Evidence uses UUID primary keys,
 * so inserting a UUID into an integer column causes a data truncation error.
 *
 * Fix: Change subject_id and causer_id to varchar(36) to support both
 * integer IDs (User) and UUID strings (Evidence).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Truncate existing logs to avoid constraint issues during column change
        DB::table('activity_log')->truncate();

        Schema::table('activity_log', function (Blueprint $table) {
            // Drop the existing integer morph columns + their index
            $table->dropIndex('subject');
            $table->dropColumn(['subject_id', 'subject_type']);

            $table->dropIndex('causer');
            $table->dropColumn(['causer_id', 'causer_type']);
        });

        Schema::table('activity_log', function (Blueprint $table) {
            // Re-add as string (varchar 36) to support both int IDs and UUIDs
            $table->string('subject_type')->nullable()->after('description');
            $table->string('subject_id', 36)->nullable()->after('subject_type');
            $table->index(['subject_type', 'subject_id'], 'subject');

            $table->string('causer_type')->nullable()->after('subject_id');
            $table->string('causer_id', 36)->nullable()->after('causer_type');
            $table->index(['causer_type', 'causer_id'], 'causer');
        });
    }

    public function down(): void
    {
        DB::table('activity_log')->truncate();

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('subject');
            $table->dropColumn(['subject_id', 'subject_type']);

            $table->dropIndex('causer');
            $table->dropColumn(['causer_id', 'causer_type']);
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
        });
    }
};
