<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * EvidenceHash Model
 *
 * Stores cryptographic hash records for evidence files.
 *
 * Security design:
 * - Records are immutable: no update() calls should ever be made on this model.
 * - The $guarded = ['id'] pattern (rather than $fillable) is intentional —
 *   all fields except the auto-increment ID are mass-assignable because this
 *   model is only ever written by trusted internal code (never user input).
 * - No soft deletes: hash records must never be removed.
 * - No updated_at: immutability is enforced at the schema level.
 * - hash_equals() must be used for comparison (timing-safe).
 *
 * @property int    $id
 * @property string $evidence_id
 * @property string $hash_value   SHA-256 hex digest (64 chars)
 * @property string $hash_type    Algorithm identifier (e.g. 'sha256')
 * @property \Carbon\Carbon $generated_at
 * @property int    $created_by
 */
class EvidenceHash extends Model
{
    use LogsActivity;

    protected $table = 'evidence_hashes';

    // No updated_at — records are immutable
    const UPDATED_AT = null;

    protected $fillable = [
        'evidence_id',
        'hash_value',
        'hash_type',
        'generated_at',
        'created_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'created_at'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** The evidence item this hash belongs to. */
    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class, 'evidence_id');
    }

    /** The user who generated this hash. */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Timing-safe comparison of this hash against a given value.
     *
     * Always use hash_equals() — never === — to prevent timing attacks
     * that could leak information about the hash value character by character.
     */
    public function matches(string $hashValue): bool
    {
        return hash_equals($this->hash_value, $hashValue);
    }

    /**
     * Return a truncated hash for display (first 16 chars + '...').
     * Never display the full hash in UI listings — reduces shoulder-surfing risk.
     */
    public function getShortHashAttribute(): string
    {
        return substr($this->hash_value, 0, 16) . '...';
    }

    // ── Activity Log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['evidence_id', 'hash_value', 'hash_type', 'created_by'])
            ->dontSubmitEmptyLogs()
            ->useLogName('evidence_hash')
            ->setDescriptionForEvent(fn (string $event) => "Evidence hash {$event}");
    }
}
