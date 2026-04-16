<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: EnsureCaseInvestigator
 *
 * Enforces the Rank 3 Senior Investigator rule at the route level:
 *   rank >= 3  AND  user is assigned to the case in the route.
 *
 * Usage in routes:
 *   ->middleware('case.investigator')
 *
 * Expects the route to have a {case} or {caseId} parameter.
 * Admins (rank >= 8) bypass the case-assignment check.
 *
 * Example route:
 *   Route::resource('cases.evidence', EvidenceController::class)
 *       ->middleware(['auth', 'verified', 'rank:3', 'case.investigator']);
 */
class EnsureCaseInvestigator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins bypass case-assignment check
        if ($user->hasMinimumRank(8)) {
            return $next($request);
        }

        // Must have at least rank 3
        if (! $user->hasMinimumRank(3)) {
            $this->logDenied($user, $request, 'insufficient_rank');
            abort(403, 'Minimum rank 3 (Senior Investigator) required.');
        }

        // Resolve case ID from route parameters
        $caseId = $request->route('case')       // model binding
            ?? $request->route('caseId')         // explicit ID param
            ?? $request->route('case_id')
            ?? $request->input('case_id');

        if (! $caseId) {
            // No case context — allow through (controller will handle further checks)
            return $next($request);
        }

        $caseId = $caseId instanceof \Illuminate\Database\Eloquent\Model
            ? $caseId->getKey()
            : (int) $caseId;

        if (! $user->isSeniorInvestigatorOnCase($caseId)) {
            $this->logDenied($user, $request, 'not_assigned_to_case', $caseId);
            abort(403, 'You are not assigned as an investigator on this case.');
        }

        return $next($request);
    }

    private function logDenied(
        \App\Models\User $user,
        Request $request,
        string $reason,
        ?int $caseId = null
    ): void {
        activity('rank_access')
            ->causedBy($user)
            ->withProperties([
                'reason'    => $reason,
                'case_id'   => $caseId,
                'user_rank' => $user->rank,
                'url'       => $request->fullUrl(),
                'ip'        => $request->ip(),
            ])
            ->log("Case investigator access denied: {$reason}");
    }
}
