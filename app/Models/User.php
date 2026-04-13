<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
