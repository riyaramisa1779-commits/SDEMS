<?php

namespace App\Http\Controllers;

use App\Jobs\CalculateEvidenceHash;
use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * EvidenceController
 *
 * Handles evidence upload, secure download, and basic preview.
 *
 * Security principles:
 * - Files are stored on the private 'evidence' disk — never publicly accessible.
 * - Original filenames are NEVER used for storage; UUID-based names are generated.
 * - All uploads are logged via Spatie Activitylog with forensic metadata.
 * - Downloads are streamed through this controller after auth/rank checks.
 * - The SHA-256 hash is computed in a background job to keep uploads fast.
 */
class EvidenceController extends Controller
{
    // ── Allowed MIME types ────────────────────────────────────────────────────

    private const ALLOWED_MIMES = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/tiff',
        'image/bmp',
        // Videos
        'video/mp4',
        'video/avi',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-matroska',
        'video/webm',
        // Audio
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
        'audio/mp4',
        // Archives / forensic
        'application/zip',
        'application/x-tar',
        'application/gzip',
        'application/x-7z-compressed',
        'application/octet-stream', // raw binary / forensic images
    ];

    // ── Upload Form ───────────────────────────────────────────────────────────

    /**
     * Show the evidence upload form.
     */
    public function create(): \Illuminate\View\View
    {
        return view('evidence.upload', [
            'categories' => Evidence::CATEGORIES,
        ]);
    }

    // ── Store (Upload) ────────────────────────────────────────────────────────

    /**
     * Handle the evidence upload.
     *
     * Flow:
     * 1. Validate metadata + file.
     * 2. Generate a UUID-based filename and store on the private 'evidence' disk.
     * 3. Create the Evidence record (model boot auto-creates ChainOfCustody).
     * 4. Dispatch CalculateEvidenceHash job to the queue.
     * 5. Log the upload via Spatie Activitylog.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // ── Validation ────────────────────────────────────────────────────────
        $validated = $request->validate([
            'case_number' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-_\/]+$/'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category'    => ['required', 'string', 'in:' . implode(',', Evidence::CATEGORIES)],
            'tags'        => ['nullable', 'string', 'max:500'],
            'file'        => [
                'required',
                'file',
                'max:2097152', // 2 GB in kilobytes
                function ($attribute, $value, $fail) {
                    $mime = $value->getMimeType();
                    if (! in_array($mime, self::ALLOWED_MIMES, true)) {
                        $fail("File type '{$mime}' is not permitted.");
                    }
                },
            ],
        ]);

        $file = $request->file('file');
        $user = Auth::user();

        // ── Secure filename ───────────────────────────────────────────────────
        // Never use the original filename for storage — UUID prevents path
        // traversal, enumeration, and filename-based information leakage.
        $extension    = $file->getClientOriginalExtension();
        $storedName   = Str::uuid() . ($extension ? ".{$extension}" : '');
        $yearMonth    = now()->format('Y/m');
        $storagePath  = "{$yearMonth}/{$storedName}";

        // ── Store file ────────────────────────────────────────────────────────
        try {
            Storage::disk('evidence')->putFileAs($yearMonth, $file, $storedName);
        } catch (\Throwable $e) {
            Log::error("Evidence upload failed for user {$user->id}: {$e->getMessage()}");
            return back()
                ->withInput()
                ->withErrors(['file' => 'File storage failed. Please try again.']);
        }

        // ── Parse tags ────────────────────────────────────────────────────────
        $tags = null;
        if (! empty($validated['tags'])) {
            $tags = array_values(array_filter(
                array_map('trim', explode(',', $validated['tags']))
            ));
        }

        // ── Create Evidence record ────────────────────────────────────────────
        // The model's boot() will automatically:
        //   1. Call generateHash() — we'll override this with the queued job.
        //   2. Create the initial ChainOfCustody 'upload' record.
        //
        // We set status = 'pending' until the hash job completes.
        $evidence = Evidence::create([
            'case_number'   => strtoupper(trim($validated['case_number'])),
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'category'      => $validated['category'],
            'tags'          => $tags,
            'file_path'     => $storagePath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'uploaded_by'   => $user->id,
            'status'        => 'pending',
        ]);

        // ── Dispatch hash job ─────────────────────────────────────────────────
        // The model boot() already called generateHash() synchronously.
        // For large files, dispatch the job to re-verify / update status.
        CalculateEvidenceHash::dispatch($evidence->id);

        // ── Activity log ──────────────────────────────────────────────────────
        activity('evidence_upload')
            ->causedBy($user)
            ->performedOn($evidence)
            ->withProperties([
                'case_number'   => $evidence->case_number,
                'title'         => $evidence->title,
                'category'      => $evidence->category,
                'file_size'     => $evidence->file_size,
                'mime_type'     => $evidence->mime_type,
                'original_name' => $evidence->original_name,
                'user_rank'     => $user->rank,
                'ip'            => $request->ip(),
                'user_agent'    => $request->userAgent(),
            ])
            ->log('Evidence uploaded');

        return redirect()
            ->route('evidence.show', $evidence)
            ->with('success', "Evidence '{$evidence->title}' uploaded successfully. Hash calculation is in progress.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    /**
     * Display evidence details with basic preview.
     */
    public function show(Evidence $evidence): \Illuminate\View\View
    {
        // Eager-load relationships needed for the view
        $evidence->load(['uploader', 'latestHash', 'latestCustody.toUser']);

        return view('evidence.show', compact('evidence'));
    }

    // ── Secure Download ───────────────────────────────────────────────────────

    /**
     * Stream the evidence file through the controller.
     *
     * Never redirects to a public URL — always streams through PHP so that:
     * - Auth and rank checks are enforced on every download.
     * - Every download is logged for chain-of-custody purposes.
     * - The storage path is never exposed to the client.
     */
    public function download(Evidence $evidence): StreamedResponse
    {
        $disk = Storage::disk('evidence');

        abort_unless($disk->exists($evidence->file_path), 404, 'Evidence file not found.');

        // Log the download as a 'checkout' custody event
        activity('evidence_download')
            ->causedBy(Auth::user())
            ->performedOn($evidence)
            ->withProperties([
                'case_number' => $evidence->case_number,
                'file_size'   => $evidence->file_size,
                'user_rank'   => Auth::user()->rank,
                'ip'          => request()->ip(),
            ])
            ->log('Evidence file downloaded');

        $filename = $evidence->original_name ?? basename($evidence->file_path);

        return $disk->download(
            $evidence->file_path,
            $filename,
            ['Content-Type' => $evidence->mime_type ?? 'application/octet-stream']
        );
    }

    // ── Preview ───────────────────────────────────────────────────────────────

    /**
     * Stream the file for inline preview (images and PDFs only).
     *
     * Returns the raw file with inline Content-Disposition so the browser
     * can render it. Only safe MIME types are allowed for preview.
     */
    public function preview(Evidence $evidence): StreamedResponse
    {
        $previewableMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'image/tiff', 'image/bmp', 'application/pdf',
        ];

        abort_unless(
            in_array($evidence->mime_type, $previewableMimes, true),
            415,
            'This file type cannot be previewed.'
        );

        $disk = Storage::disk('evidence');
        abort_unless($disk->exists($evidence->file_path), 404, 'Evidence file not found.');

        // Log preview access
        activity('evidence_preview')
            ->causedBy(Auth::user())
            ->performedOn($evidence)
            ->withProperties(['case_number' => $evidence->case_number, 'ip' => request()->ip()])
            ->log('Evidence file previewed');

        return response()->stream(function () use ($disk, $evidence) {
            $stream = $disk->readStream($evidence->file_path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => $evidence->mime_type,
            'Content-Disposition' => 'inline; filename="' . addslashes(basename($evidence->file_path)) . '"',
            'Content-Length'      => $evidence->file_size,
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
