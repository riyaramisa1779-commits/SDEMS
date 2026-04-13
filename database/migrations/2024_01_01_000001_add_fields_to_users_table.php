<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the users table with SDEMS-specific fields:
 * rank, 2FA, status, lockout, password history, soft deletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hierarchical rank 1–10 (default 1 = lowest)
            $table->unsignedTinyInteger('rank')->default(1)->after('email');

            // Account status
            $table->boolean('is_active')->default(true)->after('rank');

            // Two-factor authentication
            $table->string('two_factor_secret')->nullable()->after('is_active');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');

            // Account lockout
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('two_factor_confirmed_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');

            // Password management
            $table->timestamp('password_changed_at')->nullable()->after('locked_until');
            $table->timestamp('password_expires_at')->nullable()->after('password_changed_at');

            // Soft deletes
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'rank', 'is_active',
                'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
                'failed_login_attempts', 'locked_until',
                'password_changed_at', 'password_expires_at',
                'deleted_at',
            ]);
        });
    }
};
