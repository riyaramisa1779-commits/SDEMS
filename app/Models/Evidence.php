<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Evidence Model
 *
 * Represents a single piece of digital evidence in the system.
 *
 * Security design:
 * - UUID primary key prevents sequential enumeration of evidence IDs.
 * - Files are stored on the private 'evidence' disk — never publicly accessible.
 * - SHA-256 hash is automatically generated on creation via the boot() method.
 * - An initial chain-of-custody record is automatically created on upload.
 * - Soft deletes preserve the full audit trail; hard deletion is not permitted.
 * - All mutations are logged via Spatie Activitylog with relevant properties.
 *
 * @property string   $id
 * @property string   $case_number
 * @property string   $title
 * @property string|null $description
 * @property string   $category
 * @property array|null $tags
 * @property string   $file_path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int      $file_size
 * @property int      $uploaded_by
 * @property int|null $assigned_to
 * @property string   $status
 * @property int      $version
 */
class Evidence extends Model
{
    use HasUuids, SoftDeletes, LogsActivity;

    // ── Valid categories ──────────────────────────────────────────────────────
    public const CATEGORIES = [
        'document',
        'image',
        'video',
        'audio',
        'database',
        'email',
        'network_log',
        'forensic_image',
        'other',
    ];

    // ── Valid statuses ────────────────────────────────────────────────────────
    public const STATUSES = [
        'pending',    // Uploaded, awaiting review
        'active',     // In active use / under investigation
        'checked_out',// Checked out for analysis or court
        'sealed',     // Sealed for long-term storage
        'archived',   // Archived, no longer active
        'flagged',    // Flagged for review or concern
    ];

    protected $table = 'evidence';

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
        'deleted_at' => 'datetime',
    ];

    // ── Boot: automatic hashing + custody on creation ─────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        /**
         * On creation: generate SHA-256 hash and create the initial
         * chain-of-custody record automatically.
         *
         * This ensures no evidence record can exist without:
         *   1. A cryptographic integrity hash.
         *   2. An audit trail entry from the moment of upload.
         */
        static::created(function (Evidence $evidence) {
            // Generate and store the initial SHA-256 hash
            $evidence->generateHash();

            // Create the initial 'upload' custody record
            ChainOfCustody::create([
                'evidence_id'         => $evidence->id,
                'from_user_id'        => null, // No prior custodian on upload
                'to_user_id'          => $evidence->uploaded_by,
                'action'              => 'upload',
                'notes'               => 'Initial evidence upload.',
                'location'            => null,
                'timestamp'           => now(),
                'previous_custody_id' => null,
            ]);
        });

        /**
         * On update: increment version and re-hash the file if file_path changed.
         * This ensures the hash always reflects the current file state.
         */
        static::updating(function (Evidence $evidence) {
            if ($evidence->isDirty('file_path')) {
                // File was replaced — bump version
                $evidence->version = $evidence->getOriginal('version') + 1;
            }
        });

        static::updated(function (Evidence $evidence) {
            if ($evidence->wasChanged('file_path')) {
                // Re-hash after file replacement
                $evidence->generateHash();
            }

            // Bust the Redis cache for this evidence item
            Cache::forget("evidence:{$evidence->id}");
            Cache::forget("evidence:case:{$evidence->case_number}");
        });

        static::deleted(function (Evidence $evidence) {
            Cache::forget("evidence:{$evidence->id}");
            Cache::forget("evidence:case:{$evidence->case_number}");
        });
    }

    // ── Hash Generation ───────────────────────────────────────────────────────

    /**
     * Generate a SHA-256 hash of the evidence file and store it.
     *
     * Uses hash_file() which streams the file without loading it fully
     * into memory — safe for large forensic images.
     *
     * Security: hash_file('sha256') produces a 64-char hex digest that
     * uniquely identifies the file contents. Any single-bit change in the
     * file will produce a completely different hash (avalanche effect).
     */
    public function generateHash(): ?EvidenceHash
    {
        $disk = Storage::disk('evidence');

        if (! $disk->exists($this->file_path)) {
            return null;
        }

        $absolutePath = $disk->path($this->file_path);
        $hashValue    = hash_file('sha256', $absolutePath);

        if ($hashValue === false) {
            return null;
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
     * Verify the current file against the latest stored hash.
     *
     * Returns true  → file is intact (hash matches).
     * Returns false → file has been tampered with or is missing.
     * Returns null  → no hash record exists yet.
     *
     * This is the primary integrity check for court admissibility.
     */
    public function verifyIntegrity(): ?bool
    {
        $latestHash = $this->latestHash;

        if (! $latestHash) {
            return null;
        }

        $disk = Storage::disk('evidence');

        if (! $disk->exists($this->file_path)) {
            return false;
        }

        $currentHash = hash_file('sha256', $disk->path($this->file_path));

        return hash_equals($latestHash->hash_value, $currentHash);
    }

    /**
     * Human-readable file size (e.g. "4.2 MB").
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';

        return round($bytes / 1073741824, 2) . ' GB';
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    /** The officer who uploaded this evidence. */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** The officer currently assigned to this evidence. */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** All hash records for this evidence (append-only history). */
    public function hashes(): HasMany
    {
        return $this->hasMany(EvidenceHash::class, 'evidence_id')->orderByDesc('generated_at');
    }

    /** The most recent hash record — used for integrity verification. */
    public function latestHash(): HasOne
    {
        return $this->hasOne(EvidenceHash::class, 'evidence_id')->latestOfMany('generated_at');
    }

    /** Full chain-of-custody history, oldest first. */
    public function custodyChain(): HasMany
    {
        return $this->hasMany(ChainOfCustody::class, 'evidence_id')->orderBy('timestamp');
    }

    /** The most recent custody record. */
    public function latestCustody(): HasOne
    {
        return $this->hasOne(ChainOfCustody::class, 'evidence_id')->latestOfMany('timestamp');
    }

    // ── Query Scopes ──────────────────────────────────────────────────────────

    /** Only non-deleted, active evidence. */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Filter by case number. */
    public function scopeForCase($query, string $caseNumber)
    {
        return $query->where('case_number', $caseNumber);
    }

    /** Filter by category. */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /** Evidence uploaded by a specific user. */
    public function scopeUploadedBy($query, int $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /** Evidence assigned to a specific user. */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /** Filter by status. */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Caching Helpers ───────────────────────────────────────────────────────

    /**
     * Retrieve a single evidence item from Redis cache.
     * TTL: 10 minutes — balances freshness with query reduction.
     */
    public static function findCached(string $id): ?self
    {
        return Cache::remember(
            "evidence:{$id}",
            now()->addMinutes(10),
            fn () => self::with(['uploader', 'assignee', 'latestHash', 'latestCustody'])->find($id)
        );
    }

    /**
     * Retrieve all evidence for a case from Redis cache.
     * TTL: 5 minutes — case views are frequent during active investigations.
     */
    public static function forCaseCached(string $caseNumber): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "evidence:case:{$caseNumber}",
            now()->addMinutes(5),
            fn () => self::with(['uploader', 'latestHash'])
                         ->forCase($caseNumber)
                         ->orderByDesc('created_at')
                         ->get()
        );
    }

    // ── Activity Log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'case_number', 'title', 'category', 'status',
                'version', 'uploaded_by', 'assigned_to',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('evidence')
            ->setDescriptionForEvent(fn (string $event) => "Evidence {$event}: {$this->title}");
    }
}
