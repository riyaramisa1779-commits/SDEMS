<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Evidence Model
 *
 * Core entity of SDEMS. Represents a single piece of digital evidence.
 *
 * Security design:
 * - UUID primary key prevents sequential ID enumeration.
 * - Files are stored on the private 'evidence' disk — never publicly accessible.
 * - original_name is for display only; file_path uses UUID-based names.
 * - Soft deletes preserve the audit trail; evidence is never hard-deleted.
 * - SHA-256 hash stored in evidence_hashes table (immutable, append-only).
 * - Every state change is logged via Spatie Activitylog.
 *
 * @property string      $id            UUID
 * @property string      $case_number
 * @property string      $title
 * @property string|null $description
 * @property string      $category
 * @property array|null  $tags
 * @property string      $file_path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int         $file_size
 * @property int         $uploaded_by
 * @property int|null    $assigned_to
 * @property string      $status
 * @property int         $version
 */
class Evidence extends Model
{
    use HasUuids, SoftDeletes, LogsActivity;

    // ── Evidence categories ───────────────────────────────────────────────────

    public const CATEGORIES = [
        'document',
        'image',
        'video',
        'audio',
        'forensic_image',
        'network_capture',
        'database_export',
        'email',
        'archive',
        'other',
    ];

    // ── Valid status transitions ───────────────────────────────────────────────

    public const STATUSES = [
        'pending',    // Uploaded, hash not yet computed
        'active',     // Hash confirmed, available for review
        'in_review',  // Under active investigation
        'admitted',   // Admitted as evidence in proceedings
        'rejected',   // Rejected / inadmissible
        'archived',   // Long-term storage
    ];

    protected $fillable = [
        'case_number',
        'title',
        'description',
        'category',
        'tags',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
        'assigned_to',
        'status',
        'version',
    ];

    protected $casts = [
        'tags'       => 'array',
        'file_size'  => 'integer',
        'version'    => 'integer',
    ];

    // ── Model boot ────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // On creation: generate hash + create initial custody record
        static::created(function (Evidence $evidence) {
            // Create the initial "upload" chain-of-custody record
            ChainOfCustody::transfer(
                evidence: $evidence,
                fromUser: null,
                toUser:   User::find($evidence->uploaded_by),
                action:   'upload',
                notes:    'Initial evidence upload',
            );

            // Synchronously generate hash for small files.
            // The CalculateEvidenceHash job will re-verify for large files.
            $evidence->generateHash();
        });
    }

    // ── Activity Log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'assigned_to', 'category', 'version'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('evidence')
            ->setDescriptionForEvent(fn (string $event) => "Evidence {$event}");
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    /** The user who uploaded this evidence. */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** The user currently holding custody of this evidence. */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** All hash records for this evidence (append-only). */
    public function hashes(): HasMany
    {
        return $this->hasMany(EvidenceHash::class, 'evidence_id')->latest('generated_at');
    }

    /** The most recent hash record. */
    public function latestHash(): HasOne
    {
        return $this->hasOne(EvidenceHash::class, 'evidence_id')
                    ->latestOfMany('generated_at');
    }

    /** All chain-of-custody records for this evidence. */
    public function custodyChain(): HasMany
    {
        return $this->hasMany(ChainOfCustody::class, 'evidence_id')
                    ->orderBy('timestamp');
    }

    /** The most recent custody record. */
    public function latestCustody(): HasOne
    {
        return $this->hasOne(ChainOfCustody::class, 'evidence_id')
                    ->latestOfMany('timestamp');
    }

    // ── Hash Generation ───────────────────────────────────────────────────────

    /**
     * Compute the SHA-256 hash of the evidence file and store it.
     * Returns the EvidenceHash record, or null if the file is missing.
     */
    public function generateHash(): ?EvidenceHash
    {
        $disk = Storage::disk('evidence');

        if (! $disk->exists($this->file_path)) {
            return null;
        }

        // Stream the file to avoid loading it entirely into memory
        $stream    = $disk->readStream($this->file_path);
        $context   = hash_init('sha256');
        hash_update_stream($context, $stream);
        $hashValue = hash_final($context);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return EvidenceHash::create([
            'evidence_id'  => $this->id,
            'hash_value'   => $hashValue,
            'hash_type'    => 'sha256',
            'generated_at' => now(),
            'created_by'   => $this->uploaded_by,
        ]);
    }

    /**
     * Verify the current file matches the latest stored hash.
     * Returns true = intact, false = tampered or file missing.
     */
    public function verifyIntegrity(): bool
    {
        $latest = $this->latestHash;

        if (! $latest) {
            return false;
        }

        $disk = Storage::disk('evidence');

        if (! $disk->exists($this->file_path)) {
            return false;
        }

        $stream  = $disk->readStream($this->file_path);
        $context = hash_init('sha256');
        hash_update_stream($context, $stream);
        $current = hash_final($context);

        if (is_resource($stream)) {
            fclose($stream);
        }

        // hash_equals is timing-safe — prevents timing attacks
        return hash_equals($latest->hash_value, $current);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForCase($query, string $caseNumber)
    {
        return $query->where('case_number', strtoupper($caseNumber));
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Human-readable file size (e.g. "2.4 MB"). */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes < 1024)       return "{$bytes} B";
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';

        return round($bytes / 1073741824, 2) . ' GB';
    }

    /** Whether this evidence is in a terminal (read-only) state. */
    public function isLocked(): bool
    {
        return in_array($this->status, ['admitted', 'archived'], true);
    }
}
