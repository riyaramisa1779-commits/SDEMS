<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * ChainOfCustody Model
 *
 * Represents a single custody event in the evidence chain.
 *
 * Security design:
 * - Records are append-only and immutable: no updates, no deletes.
 * - previous_custody_id creates a cryptographically-linkable chain:
 *   any gap or insertion in the chain is detectable.
 * - The signature field is reserved for a digital signature of the
 *   custody transfer (e.g., HMAC of evidence_id + action + timestamp).
 * - No soft deletes: custody records must never be removed.
 * - No updated_at: immutability enforced at schema level.
 * - All actions are from a controlled enum — no free-text action values.
 *
 * @property int         $id
 * @property string      $evidence_id
 * @property int|null    $from_user_id
 * @property int         $to_user_id
 * @property string      $action
 * @property string|null $notes
 * @property string|null $location
 * @property \Carbon\Carbon $timestamp
 * @property string|null $signature
 * @property int|null    $previous_custody_id
 */
class ChainOfCustody extends Model
{
    use LogsActivity;

    protected $table = 'chain_of_custody';

    // No updated_at — records are immutable
    const UPDATED_AT = null;

    // Valid custody actions (mirrors the DB enum)
    public const ACTIONS = [
        'upload',
        'transfer',
        'checkout',
        'checkin',
        'review',
        'seal',
        'unseal',
        'export',
        'delete',
        'restore',
    ];

    protected $fillable = [
        'evidence_id',
        'from_user_id',
        'to_user_id',
        'action',
        'notes',
        'location',
        'timestamp',
        'signature',
        'previous_custody_id',
    ];

    protected $casts = [
        'timestamp'  => 'datetime',
        'created_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** The evidence item this custody record belongs to. */
    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class, 'evidence_id');
    }

    /** The user who transferred custody (null = system/initial upload). */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /** The user who received custody. */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * The previous custody record in the chain.
     * Traversing this relationship reconstructs the full chain.
     */
    public function previousCustody(): BelongsTo
    {
        return $this->belongsTo(ChainOfCustody::class, 'previous_custody_id');
    }

    // ── Chain Helpers ─────────────────────────────────────────────────────────

    /**
     * Get the most recent custody record for a given evidence item.
     * Used when creating a new custody record to set previous_custody_id.
     */
    public static function latestForEvidence(string $evidenceId): ?self
    {
        return static::where('evidence_id', $evidenceId)
                     ->orderByDesc('timestamp')
                     ->orderByDesc('id')
                     ->first();
    }

    /**
     * Create a new custody record, automatically linking it to the
     * previous record in the chain.
     *
     * Usage:
     *   ChainOfCustody::transfer($evidence, $fromUser, $toUser, 'transfer', 'Court handoff');
     */
    public static function transfer(
        Evidence $evidence,
        ?User    $fromUser,
        User     $toUser,
        string   $action,
        ?string  $notes    = null,
        ?string  $location = null,
    ): self {
        $previous = static::latestForEvidence($evidence->id);

        return static::create([
            'evidence_id'         => $evidence->id,
            'from_user_id'        => $fromUser?->id,
            'to_user_id'          => $toUser->id,
            'action'              => $action,
            'notes'               => $notes,
            'location'            => $location,
            'timestamp'           => now(),
            'previous_custody_id' => $previous?->id,
        ]);
    }

    /**
     * Verify the chain is unbroken from this record back to the origin.
     *
     * Returns true  → chain is intact.
     * Returns false → a gap or inconsistency was detected.
     *
     * Note: For large chains, consider a dedicated service with pagination.
     */
    public function verifyChain(): bool
    {
        $current = $this;

        while ($current->previous_custody_id !== null) {
            $previous = $current->previousCustody;

            if (! $previous) {
                // Referenced record is missing — chain is broken
                return false;
            }

            // Verify temporal ordering: previous must be earlier
            if ($previous->timestamp->isAfter($current->timestamp)) {
                return false;
            }

            $current = $previous;
        }

        // Reached the origin (previous_custody_id = null) — chain is intact
        return true;
    }

    // ── Activity Log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'evidence_id', 'from_user_id', 'to_user_id',
                'action', 'location', 'timestamp',
            ])
            ->dontSubmitEmptyLogs()
            ->useLogName('chain_of_custody')
            ->setDescriptionForEvent(fn (string $event) => "Custody {$event}: {$this->action}");
    }
}
