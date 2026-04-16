<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * EvidencePolicy
 *
 * Enforces the three-tier rank access model for evidence:
 *
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │ Rank 1–2  │ No evidence access at all                               │
 * │ Rank 3–4  │ Full CRUD — scoped to assigned cases only               │
 * │ Rank 5–7  │ Global READ-ONLY — all cases, NO writes (Auditor)       │
 * │ Rank 8+   │ Full CRUD — all cases, no restrictions (Admin)          │
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * The Auditor (rank 5–7) is "High-Rank, Low-Impact":
 * - Higher clearance than Investigator → sees everything system-wide.
 * - Programmatically blocked from create/update/delete/transfer.
 * - Can trigger Keccak-256 system-wide audit reports.
 */
class EvidencePolicy
{
    // ── List ──────────────────────────────────────────────────────────────────

    /**
     * View the evidence listing.
     *
     * Rank 3+: yes (investigators see their cases; auditors see all).
     * Rank 1–2: no.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasMinimumRank(3);
    }

    // ── Read ──────────────────────────────────────────────────────────────────

    /**
     * View a single evidence item.
     *
     * Rank 5–7 (Auditor): global read — no case-assignment check.
     * Rank 8+  (Admin):   global read — no case-assignment check.
     * Rank 3–4 (Investigator): must be assigned to the case.
     * Rank 1–2: denied.
     */
    public function view(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'view');

        // Auditors and admins: global read-only
        if ($user->canViewEvidenceGlobally()) {
            return true;
        }

        // Investigators: scoped to assigned cases
        return $user->canManageEvidenceOnCase($evidence->case_id ?? $evidence->case_number);
    }

    // ── Write — BLOCKED for Auditors ─────────────────────────────────────────

    /**
     * Upload / create new evidence.
     *
     * Auditors (rank 5–7) are STRICTLY BLOCKED — read-only clearance.
     * Rank 3–4: allowed (case-scoped check happens in controller).
     * Rank 8+:  allowed.
     */
    public function create(User $user): bool
    {
        // Explicit block for auditor band — cannot create even though rank is high
        if ($user->isReadOnlyAuditor()) {
            $this->logWriteBlocked($user, 'create');
            return false;
        }

        return $user->hasMinimumRank(3);
    }

    /**
     * Update evidence metadata.
     *
     * Auditors (rank 5–7): BLOCKED.
     * Rank 3–4: allowed on assigned cases.
     * Rank 8+:  allowed.
     */
    public function update(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'update');

        if ($user->isReadOnlyAuditor()) {
            $this->logWriteBlocked($user, 'update', $evidence);
            return false;
        }

        return $user->canManageEvidenceOnCase($evidence->case_id ?? $evidence->case_number);
    }

    /**
     * Soft-delete evidence.
     *
     * Auditors (rank 5–7): BLOCKED.
     * Rank 3–4: allowed on assigned cases.
     * Rank 8+:  allowed.
     */
    public function delete(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'delete');

        if ($user->isReadOnlyAuditor()) {
            $this->logWriteBlocked($user, 'delete', $evidence);
            return false;
        }

        return $user->canManageEvidenceOnCase($evidence->case_id ?? $evidence->case_number);
    }

    /**
     * Restore soft-deleted evidence.
     *
     * Auditors (rank 5–7): BLOCKED.
     * Rank 5+ non-auditor (i.e. rank 8+): allowed.
     */
    public function restore(User $user, Model $evidence): bool
    {
        if ($user->isReadOnlyAuditor()) {
            $this->logWriteBlocked($user, 'restore', $evidence);
            return false;
        }

        return $user->hasMinimumRank(8);
    }

    /**
     * Chain of Custody transfer.
     *
     * Auditors (rank 5–7): BLOCKED — they observe the chain, never modify it.
     * Rank 3–4: allowed on assigned cases.
     * Rank 8+:  allowed.
     */
    public function transferCustody(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'custody_transfer');

        if ($user->isReadOnlyAuditor()) {
            $this->logWriteBlocked($user, 'custody_transfer', $evidence);
            return false;
        }

        return $user->canTransferCustody($evidence->case_id ?? $evidence->case_number);
    }

    // ── Audit-specific capabilities ───────────────────────────────────────────

    /**
     * Verify hash integrity on a single evidence item.
     *
     * Rank 3–4: allowed on assigned cases (SHA-256).
     * Rank 5+:  allowed globally (Keccak-256 system-wide audit).
     */
    public function verifyIntegrity(User $user, Model $evidence): bool
    {
        $this->logAccessAttempt($user, $evidence, 'integrity_check');

        // Auditors and admins: global integrity check
        if ($user->canViewEvidenceGlobally()) {
            return true;
        }

        // Investigators: scoped to assigned cases
        return $user->canVerifyEvidenceIntegrity($evidence->case_id ?? $evidence->case_number);
    }

    /**
     * Trigger a system-wide audit report (Keccak-256 batch verification).
     *
     * Rank 5+ only — this is the core Auditor capability.
     * Rank 3–4 investigators cannot run system-wide audits.
     */
    public function runSystemAudit(User $user): bool
    {
        $granted = $user->canRunSystemAudit();

        activity('audit_access')
            ->causedBy($user)
            ->withProperties([
                'action'    => 'system_audit',
                'user_rank' => $user->rank,
                'granted'   => $granted,
            ])
            ->log("System audit triggered by rank-{$user->rank} user");

        return $granted;
    }

    // ── Logging helpers ───────────────────────────────────────────────────────

    /**
     * Log every rank-based access attempt (required by spec).
     */
    private function logAccessAttempt(User $user, Model $evidence, string $action): void
    {
        $caseRef = $evidence->case_id ?? $evidence->case_number ?? null;

        activity('evidence_access')
            ->causedBy($user)
            ->withProperties([
                'action'      => $action,
                'evidence_id' => $evidence->getKey(),
                'case_ref'    => $caseRef,
                'user_rank'   => $user->rank,
                'is_auditor'  => $user->isReadOnlyAuditor(),
                'granted'     => $user->canViewEvidenceGlobally()
                                 || $user->canManageEvidenceOnCase($caseRef ?? 0),
            ])
            ->log("Evidence {$action} attempted by rank-{$user->rank} user");
    }

    /**
     * Log a blocked write attempt by an auditor.
     * Creates a clear forensic record that the auditor tried to modify evidence.
     */
    private function logWriteBlocked(User $user, string $action, ?Model $evidence = null): void
    {
        activity('audit_write_blocked')
            ->causedBy($user)
            ->withProperties([
                'action'      => $action,
                'evidence_id' => $evidence?->getKey(),
                'user_rank'   => $user->rank,
                'reason'      => 'Auditor (rank 5–7) is read-only — write operations are blocked',
            ])
            ->log("BLOCKED: Auditor rank-{$user->rank} attempted {$action} on evidence");
    }
}
