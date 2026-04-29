<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add full-text search indexes to the evidence table for Module 7.
     * These indexes improve search performance on case_number, title, and description.
     */
    public function up(): void
    {
        // Add full-text index for search functionality (MySQL/MariaDB only)
        // SQLite doesn't support FULLTEXT indexes, so we skip for testing
        // Regular indexes already exist from previous migrations
        if (DB::getDriverName() !== 'sqlite') {
            // Check if fulltext index already exists
            $indexes = DB::select("SHOW INDEX FROM evidence WHERE Key_name = 'search_index'");
            if (empty($indexes)) {
                DB::statement('ALTER TABLE evidence ADD FULLTEXT search_index (case_number, title, description)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop full-text index
        if (DB::getDriverName() !== 'sqlite') {
            $indexes = DB::select("SHOW INDEX FROM evidence WHERE Key_name = 'search_index'");
            if (!empty($indexes)) {
                DB::statement('ALTER TABLE evidence DROP INDEX search_index');
            }
        }
    }
};
