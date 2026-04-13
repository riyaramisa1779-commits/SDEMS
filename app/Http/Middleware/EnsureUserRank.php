<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: EnsureUserRank
 *
 * Usage in routes: ->middleware('rank:5')
 * Denies access if the authenticated user's rank is below the minimum.
 */
class EnsureUserRank
{
    public function handle(Request $request, Closure $next, int $minRank = 1): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasMinimumRank($minRank)) {
            // Log the denied rank-based access attempt
            activity('rank_access')
                ->causedBy($user)
                ->withProperties([
                    'required_rank' => $minRank,
                    'user_rank'     => $user->rank,
                    'url'           => $request->fullUrl(),
                    'ip'            => $request->ip(),
                ])
                ->log("Rank access denied: required {$minRank}, user has {$user->rank}");

            abort(403, "Insufficient rank. Minimum rank {$minRank} required.");
        }

        return $next($request);
    }
}
