<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register User Policy
        Gate::policy(User::class, UserPolicy::class);

        // Register Evidence Policy (Rank 3 Senior Investigator scope)
        // The Evidence model will be created in Module 2 — policy is ready now.
        Gate::policy(\App\Models\Evidence::class, \App\Policies\EvidencePolicy::class);

        // ── Riya's Special Profile Page ──────────────────────────────────────
        // Access is granted if the authenticated user is named "Riya"
        // OR has a rank >= 3 (Senior Investigator and above).
        Gate::define('access-riya-profile', function (User $user): bool {
            return strtolower($user->name) === 'riya' || $user->hasMinimumRank(3);
        });

        // ── Nusrath's Special Profile Page ───────────────────────────────────
        // Same rule: user named "Nusrath" OR rank >= 3.
        Gate::define('access-nusrath-profile', function (User $user): bool {
            return strtolower($user->name) === 'nusrath' || $user->hasMinimumRank(3);
        });

        // Use Tailwind pagination
        Paginator::useTailwind();

        // Global password strength rule
        Password::defaults(function () {
            return Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols();
            // Note: ->uncompromised() removed to avoid external API calls in tests
        });
    }
}
