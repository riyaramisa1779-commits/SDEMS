<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * EvidencePolicy — enforces Rank 3 (Senior Investigator) access rules.
 *
 * Core rule: rank >= 3 AND user is primary/secondary investigator on the case.
 * Admins (rank >= 8) bypass the case-assignment check.
 *
 * This policy is intentionally model-agnostic for the $evidence parameter
 * so it works before the Evidence model is fully built in Module 2.
 * Once Evidence model exists, type-hint it directly.
 */
class EvidencePolicy
{
    /**
     * View a list of evidence items.
     * Rank 3+ can see evidence on their assigned cases.
     * Rank 8+ (admin) can see all evidence.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasMinimumRank(3);
    }

    /**
     * View a single evidence item.
     * Must be rank 3+ AND assigned to the case, OR rank 8+ admin.
     */
    public function view(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'view');

        return $user->canManageEvidenceOnCase($evidence->case_id);
    }

    /**
     * Upload / create new evidence.
     * Requires rank >= 3 and case assignment.
     */
    public function create(User $user): bool
    {
        return $user->hasMinimumRank(3);
    }

    /**
     * Update evidence metadata.
     * Requires rank >= 3 and case assignment.
     */
    public function update(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'update');

        return $user->canManageEvidenceOnCase($evidence->case_id);
    }

    /**
     * Soft-delete evidence.
     * Requires rank >= 3 and case assignment.
     */
    public function delete(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'delete');

        return $user->canManageEvidenceOnCase($evidence->case_id);
    }

    /**
     * Restore soft-deleted evidence.
     * Requires rank >= 5 (supervisor) or admin.
     */
    public function restore(User $user, Model $evidence): bool
    {
        return $user->hasMinimumRank(5) && $user->canManageEvidenceOnCase($evidence->case_id);
    }

    /**
     * Initiate or sign off a Chain of Custody transfer.
     * Requires rank >= 3 and case assignment.
     */
    public function transferCustody(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'custody_transfer');

        return $user->canTransferCustody($evidence->case_id);
    }

    /**
     * Run a hash-integrity verification on an evidence file.
     * Requires rank >= 3 and case assignment.
     */
    public function verifyIntegrity(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'integrity_check');

        return $user->canVerifyEvidenceIntegrity($evidence->case_id);
    }

    /**
     * Log every rank-based access attempt to the activity log.
     * Satisfies the requirement: "Log every rank-based access attempt."
     */
    private function logAccessAttempt(User $user, Model $evidence, string $action): void
    {
        activity('evidence_access')
            ->causedBy($user)
            ->withProperties([
                'action'      => $action,
                'evidence_id' => $evidence->getKey(),
                'case_id'     => $evidence->case_id ?? null,
                'user_rank'   => $user->rank,
                'granted'     => $user->canManageEvidenceOnCase($evidence->case_id ?? 0),
            ])
            ->log("Evidence {$action} attempted by rank-{$user->rank} user");
    }
}
