<?php

namespace App\Http\Controllers;

use App\Models\ChainOfCustody;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ChainOfCustodyController
 *
 * Manages all custody lifecycle actions: viewing chains, transferring custody,
 * checking evidence in and out.
 *
 * Access control:
 * - index / show (full chain): rank 5+ (auditors) OR rank 3+ on assigned evidence
 * - transfer: rank 3+ (current custodian or admin)
 * - checkout / checkin: rank 3+
 *
 * All actions are logged via Spatie Activitylog.
 */
class ChainOfCustodyController extends Controller
{
    // ── Evidence Index ────────────────────────────────────────────────────────

    /**
     * List all evidence with current custody status.
     * Rank 5+ sees all evidence; rank 3–4 sees only their assigned evidence.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $user  = Auth::user();
        $query = Evidence::with(['uploader', 'custodian', 'latestCustody.toUser', 'latestHash'])
                         ->withCount('custodyChain');

        // Rank 5+ (auditors/admins) see all evidence
        // Rank 3–4 see only evidence they uploaded or are assigned to
        if (! $user->hasMinimumRank(5)) {
            $query->where(function ($q) use ($user) {
                $q->where('uploaded_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('case_number')) {
            $query->where('case_number', 'like', '%' . $request->case_number . '%');
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('case_number', 'like', "%{$term}%");
            });
        }

        $evidence = $query->latest()->paginate(15)->withQueryString();

        // Users eligible to receive custody (rank 3+, active)
        $eligibleUsers = User::where('is_active', true)
                             ->where('rank', '>=', 3)
                             ->orderBy('rank', 'desc')
                             ->orderBy('name')
                             ->get(['id', 'name', 'rank']);

        return view('custody.index', compact('evidence', 'eligibleUsers'));
    }

    // ── Chain of Custody Timeline ─────────────────────────────────────────────

    /**
     * Show the full chain of custody timeline for a specific evidence item.
     * Rank 5+ can view any evidence; rank 3–4 only their assigned evidence.
     */
    public function show(Evidence $evidence): \Illuminate\View\View
    {
        $user = Auth::user();

        // Access check: rank 5+ sees all; rank 3–4 only assigned/uploaded
        if (! $user->hasMinimumRank(5)) {
            abort_unless(
                $evidence->uploaded_by === $user->id || $evidence->assigned_to === $user->id,
                403,
                'You do not have access to this evidence chain.'
            );
        }

        // Load full chain with user relationships
        $chain = ChainOfCustody::where('evidence_id', $evidence->id)
                               ->with(['fromUser', 'toUser', 'previousCustody'])
                               ->orderBy('timestamp')
                               ->get();

        // Gap detection: check if each transfer's from_user matches previous to_user
        $gaps = [];
        foreach ($chain as $i => $record) {
            if ($i === 0) continue; // First record (upload) has no previous
            $prev = $chain[$i - 1];
            if ($record->from_user_id !== null && $prev->to_user_id !== $record->from_user_id) {
                $gaps[] = $record->id;
            }
        }

        // Verify chain integrity (linked-list check)
        $chainIntact = $chain->isNotEmpty() ? $chain->last()->verifyChain() : true;

        // Users eligible to receive custody (rank 3+, active, not current custodian)
        $eligibleUsers = User::where('is_active', true)
                             ->where('rank', '>=', 3)
                             ->where('id', '!=', $evidence->assigned_to)
                             ->orderBy('rank', 'desc')
                             ->orderBy('name')
                             ->get(['id', 'name', 'rank']);

        $evidence->load(['uploader', 'custodian', 'latestHash']);

        // Log the chain view
        activity('chain_of_custody')
            ->causedBy($user)
            ->performedOn($evidence)
            ->withProperties([
                'case_number' => $evidence->case_number,
                'chain_length' => $chain->count(),
                'ip' => request()->ip(),
            ])
            ->log('Chain of custody viewed');

        return view('custody.show', compact(
            'evidence', 'chain', 'gaps', 'chainIntact', 'eligibleUsers'
        ));
    }

    // ── Transfer Custody ──────────────────────────────────────────────────────

    /**
     * Transfer custody of evidence to another user.
     * Requires rank 3+ and the user must be the current custodian or rank 8+.
     */
    public function transfer(Request $request, Evidence $evidence): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // Must be rank 3+
        abort_unless($user->hasMinimumRank(3), 403, 'Minimum rank 3 required for custody transfers.');

        // Must be current custodian OR admin (rank 8+)
        $isCustodian = $evidence->assigned_to === $user->id;
        $isAdmin     = $user->hasMinimumRank(8);
        abort_unless($isCustodian || $isAdmin, 403, 'Only the current custodian or an admin can transfer custody.');

        // Evidence must not be locked
        abort_if($evidence->isLocked(), 403, 'Locked evidence cannot be transferred.');

        $validated = $request->validate([
            'to_user_id'    => ['required', 'integer', 'exists:users,id'],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'location'      => ['nullable', 'string', 'max:255'],
            'acknowledged'  => ['required', 'accepted'],
        ]);

        $toUser = User::findOrFail($validated['to_user_id']);

        // Prevent transferring to self
        if ($toUser->id === $user->id) {
            return back()->withErrors(['to_user_id' => 'You cannot transfer custody to yourself.']);
        }

        // Prevent transferring to inactive users
        if (! $toUser->is_active) {
            return back()->withErrors(['to_user_id' => 'Cannot transfer custody to an inactive user.']);
        }

        DB::transaction(function () use ($evidence, $user, $toUser, $validated) {
            // Create custody record
            ChainOfCustody::transfer(
                evidence: $evidence,
                fromUser: $user,
                toUser:   $toUser,
                action:   'transfer',
                notes:    $validated['notes'] ?? null,
                location: $validated['location'] ?? null,
            );

            // Update evidence assignment
            $evidence->update(['assigned_to' => $toUser->id]);
        });

        // Activity log
        activity('chain_of_custody')
            ->causedBy($user)
            ->performedOn($evidence)
            ->withProperties([
                'case_number'  => $evidence->case_number,
                'from_user'    => $user->name,
                'to_user'      => $toUser->name,
                'to_user_rank' => $toUser->rank,
                'location'     => $validated['location'] ?? null,
                'ip'           => $request->ip(),
            ])
            ->log("Custody transferred from {$user->name} to {$toUser->name}");

        return redirect()
            ->route('custody.show', $evidence)
            ->with('success', "Custody of '{$evidence->title}' transferred to {$toUser->name}.");
    }

    // ── Check Out ─────────────────────────────────────────────────────────────

    /**
     * Check out evidence (e.g., for court, lab analysis).
     * The custodian remains the same; this records the checkout event.
     */
    public function checkout(Request $request, Evidence $evidence): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        abort_unless($user->hasMinimumRank(3), 403, 'Minimum rank 3 required.');
        abort_unless(
            $evidence->assigned_to === $user->id || $user->hasMinimumRank(8),
            403,
            'Only the current custodian can check out evidence.'
        );
        abort_if($evidence->isLocked(), 403, 'Locked evidence cannot be checked out.');

        $validated = $request->validate([
            'notes'    => ['nullable', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        ChainOfCustody::transfer(
            evidence: $evidence,
            fromUser: $user,
            toUser:   $user,  // Checkout: same user, records the event
            action:   'checkout',
            notes:    $validated['notes'] ?? null,
            location: $validated['location'],
        );

        // Update status to in_review if currently active
        if ($evidence->status === 'active') {
            $evidence->update(['status' => 'in_review']);
        }

        activity('chain_of_custody')
            ->causedBy($user)
            ->performedOn($evidence)
            ->withProperties([
                'case_number' => $evidence->case_number,
                'location'    => $validated['location'],
                'ip'          => $request->ip(),
            ])
            ->log("Evidence checked out by {$user->name} at {$validated['location']}");

        return redirect()
            ->route('custody.show', $evidence)
            ->with('success', "Evidence '{$evidence->title}' checked out successfully.");
    }

    // ── Check In ──────────────────────────────────────────────────────────────

    /**
     * Check in evidence (return after checkout).
     */
    public function checkin(Request $request, Evidence $evidence): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        abort_unless($user->hasMinimumRank(3), 403, 'Minimum rank 3 required.');
        abort_unless(
            $evidence->assigned_to === $user->id || $user->hasMinimumRank(8),
            403,
            'Only the current custodian can check in evidence.'
        );

        $validated = $request->validate([
            'notes'    => ['nullable', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        ChainOfCustody::transfer(
            evidence: $evidence,
            fromUser: $user,
            toUser:   $user,
            action:   'checkin',
            notes:    $validated['notes'] ?? null,
            location: $validated['location'],
        );

        // Revert status to active if it was in_review
        if ($evidence->status === 'in_review') {
            $evidence->update(['status' => 'active']);
        }

        activity('chain_of_custody')
            ->causedBy($user)
            ->performedOn($evidence)
            ->withProperties([
                'case_number' => $evidence->case_number,
                'location'    => $validated['location'],
                'ip'          => $request->ip(),
            ])
            ->log("Evidence checked in by {$user->name} at {$validated['location']}");

        return redirect()
            ->route('custody.show', $evidence)
            ->with('success', "Evidence '{$evidence->title}' checked in successfully.");
    }
}
