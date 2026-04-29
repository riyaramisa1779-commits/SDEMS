<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Services\EvidenceIntegrityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * IntegrityController
 *
 * Handles the Integrity Verification module (Module 5).
 *
 * Route protection:
 * - index()      → rank 5+ (Auditors and above can view integrity reports)
 * - show()       → rank 5+
 * - verify()     → rank 5+ (triggers a single verification + logs it)
 * - rehash()     → rank 8+ (re-generates the stored hash — admin only)
 * - bulkVerify() → rank 8+ (heavy operation — admin only)
 *
 * All routes require auth + verified + account.locked middleware (set in web.php).
 */
class IntegrityController extends Controller
{
    public function __construct(
        private readonly EvidenceIntegrityService $integrityService
    ) {}

    // ── Dashboard ─────────────────────────────────────────────────────────────

    /**
     * Integrity dashboard — lists all evidence with their current status.
     * Rank 5+ required.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $query = Evidence::with(['latestHash', 'uploader'])
            ->orderBy('created_at', 'desc');

        // Optional filters
        if ($request->filled('case_number')) {
            $query->where('case_number', 'like', '%' . $request->case_number . '%');
        }

        if ($request->filled('status_filter')) {
            match ($request->status_filter) {
                'pending'  => $query->whereDoesntHave('hashes', fn ($q) => $q->where('hash_type', 'sha256')),
                'has_hash' => $query->whereHas('hashes', fn ($q) => $q->where('hash_type', 'sha256')),
                default    => null,
            };
        }

        $evidenceList = $query->paginate(20)->withQueryString();

        // Summary counts (lightweight — no file reads)
        $stats = $this->integrityService->getDashboardStats();

        return view('integrity.index', compact('evidenceList', 'stats'));
    }

    // ── Single Evidence Detail ────────────────────────────────────────────────

    /**
     * Detailed integrity report for a single evidence item.
     * Rank 5+ required.
     */
    public function show(Evidence $evidence): \Illuminate\View\View
    {
        $evidence->load(['uploader', 'latestHash', 'latestCustody.toUser']);

        // Load all hash records for the timeline
        $hashHistory = $evidence->hashes()
            ->with('generatedBy')
            ->orderBy('generated_at', 'desc')
            ->get();

        // Load recent integrity-related activity logs for this evidence
        $verificationLogs = \Spatie\Activitylog\Models\Activity::query()
            ->whereIn('log_name', ['integrity_verified', 'integrity_alert', 'integrity_rehash'])
            ->where('subject_type', Evidence::class)
            ->where('subject_id', $evidence->id)
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('integrity.show', compact('evidence', 'hashHistory', 'verificationLogs'));
    }

    // ── AJAX: Verify Single ───────────────────────────────────────────────────

    /**
     * Manually verify a single evidence file.
     * Returns JSON for Alpine.js AJAX calls.
     * Rank 5+ required.
     */
    public function verify(Evidence $evidence): JsonResponse
    {
        $user   = Auth::user();
        $result = $this->integrityService->verifyAndLog($evidence, $user);

        return response()->json([
            'status'        => $result['status'],
            'passed'        => $result['passed'],
            'message'       => $result['message'],
            'stored_hash'   => $result['stored_hash'],
            'computed_hash' => $result['computed_hash'],
            'verified_at'   => $result['verified_at']->format('d M Y, H:i:s'),
        ]);
    }

    // ── Re-hash (Admin Only) ──────────────────────────────────────────────────

    /**
     * Re-generate the SHA-256 hash for an evidence file.
     * Creates a new hash record (append-only — never overwrites).
     * Rank 8+ required.
     */
    public function rehash(Evidence $evidence): JsonResponse
    {
        $user = Auth::user();

        // Extra guard — middleware handles this, but belt-and-suspenders
        if (! $user->hasMinimumRank(8)) {
            return response()->json(['error' => 'Insufficient rank for re-hash operation.'], 403);
        }

        $hashRecord = $this->integrityService->regenerateHash($evidence, $user);

        if (! $hashRecord) {
            return response()->json([
                'error' => 'Re-hash failed. Evidence file may be missing from storage.',
            ], 422);
        }

        return response()->json([
            'success'      => true,
            'new_hash'     => $hashRecord->hash_value,
            'generated_at' => $hashRecord->generated_at->format('d M Y, H:i:s'),
            'message'      => 'Hash successfully re-generated and stored.',
        ]);
    }

    // ── Bulk Verify ───────────────────────────────────────────────────────────

    /**
     * Run a bulk integrity check across all evidence.
     * Returns JSON summary. Rank 8+ required (heavy operation).
     */
    public function bulkVerify(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user->hasMinimumRank(8)) {
            return response()->json(['error' => 'Insufficient rank for bulk verification.'], 403);
        }

        $report = $this->integrityService->bulkVerify($user);

        return response()->json([
            'total'    => $report['total'],
            'verified' => $report['verified'],
            'tampered' => $report['tampered'],
            'pending'  => $report['pending'],
            'missing'  => $report['missing'],
            'run_at'   => $report['run_at']->format('d M Y, H:i:s'),
            'items'    => $report['items']->map(fn ($item) => [
                'evidence_id'   => $item['evidence_id'],
                'case_number'   => $item['case_number'],
                'title'         => $item['title'],
                'status'        => $item['status'],
                'passed'        => $item['passed'],
                'message'       => $item['message'],
            ])->values(),
        ]);
    }
}
