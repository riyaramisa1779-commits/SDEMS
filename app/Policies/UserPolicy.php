<?php

namespace App\Policies;

use App\Models\User;

/**
 * Authorization policy for User management.
 * Combines role checks (Spatie) with rank checks.
 */
class UserPolicy
{
    /**
     * Admins with rank >= 8 can view the user list.
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole('admin') && $authUser->hasMinimumRank(8);
    }

    /**
     * Admins can view any user profile.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        return $authUser->hasRole('admin') && $authUser->hasMinimumRank(8);
    }

    /**
     * Admins with rank >= 8 can create users.
     */
    public function create(User $authUser): bool
    {
        return $authUser->hasRole('admin') && $authUser->hasMinimumRank(8);
    }

    /**
     * Admins can update users, but cannot escalate rank beyond their own.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        if (! $authUser->hasRole('admin') || ! $authUser->hasMinimumRank(8)) {
            return false;
        }

        // Cannot edit a user with equal or higher rank (unless super-admin)
        if ($targetUser->rank >= $authUser->rank && ! $authUser->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    /**
     * Only super-admin or rank 10 can delete users.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        if ($authUser->id === $targetUser->id) {
            return false; // Cannot delete yourself
        }

        return $authUser->hasRole('super-admin') || $authUser->rank >= 10;
    }

    /**
     * Restore soft-deleted users.
     */
    public function restore(User $authUser, User $targetUser): bool
    {
        return $authUser->hasRole('super-admin') || $authUser->rank >= 10;
    }

    /**
     * Assign roles — only super-admin or rank >= 9.
     */
    public function assignRoles(User $authUser, User $targetUser): bool
    {
        return $authUser->hasRole('super-admin') || $authUser->hasMinimumRank(9);
    }

    /**
     * Change rank — only super-admin or rank 10 can change ranks.
     * Prevents privilege escalation.
     */
    public function changeRank(User $authUser, User $targetUser, int $newRank): bool
    {
        if (! $authUser->hasRole('super-admin') && $authUser->rank < 10) {
            return false;
        }

        // Cannot assign rank higher than own rank
        return $newRank <= $authUser->rank;
    }
}
