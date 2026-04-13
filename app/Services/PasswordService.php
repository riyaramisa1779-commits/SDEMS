<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Handles password policy enforcement and history tracking.
 */
class PasswordService
{
    // How many previous passwords to remember
    public const HISTORY_LIMIT = 5;

    // Password expires after this many days (0 = never)
    public const EXPIRY_DAYS = 90;

    /**
     * Validate a new password against the policy.
     * Returns an array of error messages (empty = valid).
     */
    public function validate(string $password, User $user): array
    {
        $errors = [];

        if (strlen($password) < 12) {
            $errors[] = 'Password must be at least 12 characters.';
        }

        if (! preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (! preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if (! preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if (! preg_match('/[\W_]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        if ($this->isPasswordReused($password, $user)) {
            $errors[] = 'You cannot reuse any of your last ' . self::HISTORY_LIMIT . ' passwords.';
        }

        return $errors;
    }

    /**
     * Check if the password was used recently.
     */
    public function isPasswordReused(string $password, User $user): bool
    {
        return $user->passwordHistories()
            ->limit(self::HISTORY_LIMIT)
            ->get()
            ->contains(fn ($history) => Hash::check($password, $history->password));
    }

    /**
     * Store the current password in history and update expiry.
     */
    public function recordPasswordChange(User $user, string $newHashedPassword): void
    {
        // Save to history
        PasswordHistory::create([
            'user_id'  => $user->id,
            'password' => $newHashedPassword,
        ]);

        // Prune old history beyond limit (SQLite-compatible)
        $keepIds = $user->passwordHistories()
            ->limit(self::HISTORY_LIMIT)
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            PasswordHistory::where('user_id', $user->id)
                ->whereNotIn('id', $keepIds)
                ->delete();
        }

        // Update timestamps
        $user->update([
            'password_changed_at' => now(),
            'password_expires_at' => self::EXPIRY_DAYS > 0
                ? now()->addDays(self::EXPIRY_DAYS)
                : null,
        ]);
    }
}
