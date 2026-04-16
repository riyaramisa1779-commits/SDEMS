<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rank',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
        'password_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'two_factor_confirmed_at'  => 'datetime',
            'locked_until'             => 'datetime',
            'password_changed_at'      => 'datetime',
            'password_expires_at'      => 'datetime',
            'password'                 => 'hashed',
            'is_active'                => 'boolean',
        ];
    }

    // ─── Activity Log ────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'rank', 'is_active', 'email_verified_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "User {$eventName}");
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class)->latest();
    }

    public function loginDevices(): HasMany
    {
        return $this->hasMany(LoginDevice::class)->latest();
    }

    // ─── Evidence Relationships ───────────────────────────────────────────────

    /** Evidence items uploaded by this user. */
    public function uploadedEvidence(): HasMany
    {
        return $this->hasMany(\App\Models\Evidence::class, 'uploaded_by');
    }

    /** Evidence items currently assigned to this user. */
    public function assignedEvidence(): HasMany
    {
        return $this->hasMany(\App\Models\Evidence::class, 'assigned_to');
    }

    /** All custody records where this user received evidence. */
    public function custodyReceived(): HasMany
    {
        return $this->hasMany(\App\Models\ChainOfCustody::class, 'to_user_id');
    }

    /** All custody records where this user transferred evidence. */
    public function custodyTransferred(): HasMany
    {
        return $this->hasMany(\App\Models\ChainOfCustody::class, 'from_user_id');
    }

    // ─── Rank Helpers ────────────────────────────────────────────────────────

    /**
     * Check if this user's rank is strictly higher than the given rank.
     */
    public function hasHigherRankThan(int $rank): bool
    {
        return $this->rank > $rank;
    }

    /**
     * Check if this user's rank meets or exceeds the minimum required.
     */
    public function hasMinimumRank(int $minRank): bool
    {
        return $this->rank >= $minRank;
    }

    /**
     * Rank 3 — Senior Investigator operational check.
     *
     * Returns true when BOTH conditions are met:
     *   1. User rank >= 3
     *   2. User is the primary OR secondary investigator on the given case.
     *
     * This is the core gate for all evidence CRUD, custody transfers,
     * and hash-integrity checks scoped to a specific case.
     *
     * Usage:
     *   $user->isSeniorInvestigatorOnCase($case)
     *   // or pass just the case ID:
     *   $user->isSeniorInvestigatorOnCase($caseId)
     */
    public function isSeniorInvestigatorOnCase(mixed $case): bool
    {
        if (! $this->hasMinimumRank(3)) {
            return false;
        }

        $caseId = $case instanceof \Illuminate\Database\Eloquent\Model
            ? $case->getKey()
            : (int) $case;

        // Lazy-load the cases relationship only when the model exists.
        // The actual relationship is defined in the Case model (Module 2).
        // We query directly to avoid a hard dependency on the Case model here.
        return \DB::table('cases')
            ->where('id', $caseId)
            ->where(function ($q) {
                $q->where('primary_investigator_id', $this->id)
                  ->orWhere('secondary_investigator_id', $this->id);
            })
            ->exists();
    }

    /**
     * Convenience: can this user perform full evidence operations on a case?
     *
     * Rule: rank >= 3 AND assigned to case  (Senior Investigator scope)
     * Higher ranks (>= 8 admin) bypass the case-assignment check entirely.
     */
    public function canManageEvidenceOnCase(mixed $case): bool
    {
        // Admins (rank >= 8) have unrestricted evidence access
        if ($this->hasMinimumRank(8)) {
            return true;
        }

        return $this->isSeniorInvestigatorOnCase($case);
    }

    /**
     * Can this user initiate a Chain of Custody transfer?
     *
     * Requires rank >= 3 AND assignment to the case the evidence belongs to.
     */
    public function canTransferCustody(mixed $case): bool
    {
        return $this->canManageEvidenceOnCase($case);
    }

    /**
     * Can this user run a hash-integrity verification on evidence?
     *
     * Same scope as evidence management — rank >= 3 + case assignment.
     */
    public function canVerifyEvidenceIntegrity(mixed $case): bool
    {
        return $this->canManageEvidenceOnCase($case);
    }

    // ─── Rank 5 — Legal Consultant / Auditor ─────────────────────────────────

    /**
     * Global read-only access for Legal Consultants / Auditors (rank >= 5).
     *
     * They can VIEW any evidence across ALL cases — no case-assignment needed.
     * They are STRICTLY BLOCKED from create, update, delete, and custody transfers.
     *
     * System logic: if (UserRank >= 5) -> Allow Global Read-Only; Deny Write.
     */
    public function isAuditor(): bool
    {
        return $this->hasMinimumRank(5);
    }

    /**
     * Can this user view evidence globally (across all cases)?
     *
     * Rank 5+ (Auditor) → yes, all evidence system-wide.
     * Rank 3–4 (Investigator) → only on assigned cases.
     * Rank 1–2 → no.
     */
    public function canViewEvidenceGlobally(): bool
    {
        return $this->hasMinimumRank(5);
    }

    /**
     * Is this user strictly read-only on evidence?
     *
     * Rank 5–7: auditor/consultant — global read, NO writes.
     * Rank 8+:  admin — full write access, not read-only.
     * Rank 3–4: investigator — scoped write on assigned cases.
     *
     * Returns true only for the "high-rank, low-impact" band (5–7).
     */
    public function isReadOnlyAuditor(): bool
    {
        return $this->rank >= 5 && $this->rank < 8;
    }

    /**
     * Can this user trigger system-wide audit reports and
     * Keccak-256 hash verification across all evidence?
     *
     * Requires rank >= 5 (Auditor clearance).
     */
    public function canRunSystemAudit(): bool
    {
        return $this->hasMinimumRank(5);
    }

    // ─── Account Lockout ─────────────────────────────────────────────────────

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_attempts');

        // Lock after 5 failed attempts for 30 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->update(['locked_until' => now()->addMinutes(30)]);
        }
    }

    public function resetFailedLogins(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);
    }

    // ─── 2FA ─────────────────────────────────────────────────────────────────

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    // ─── Password ────────────────────────────────────────────────────────────

    public function isPasswordExpired(): bool
    {
        return $this->password_expires_at !== null && $this->password_expires_at->isPast();
    }

    public function passwordExpiresInDays(): ?int
    {
        if ($this->password_expires_at === null) {
            return null;
        }

        return (int) now()->diffInDays($this->password_expires_at, false);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRank($query, int $rank)
    {
        return $query->where('rank', $rank);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
