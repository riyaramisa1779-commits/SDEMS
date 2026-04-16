<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg text-slate-800 dark:text-slate-200">
                Welcome back, {{ Auth::user()->name }}
            </h2>
            <x-rank-badge :rank="Auth::user()->rank"/>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ── Role/Rank context banner ─────────────────────────────────── --}}
        @php $user = Auth::user(); @endphp

        @if($user->rank >= 8)
        <div class="flex items-center gap-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 px-5 py-3">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <p class="text-sm text-purple-800 dark:text-purple-300">
                <span class="font-semibold">Administrator</span> — You have full system access including user management, role assignment, and unrestricted evidence access.
            </p>
        </div>

        @elseif($user->rank >= 3)
        <div class="flex items-center gap-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-5 py-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-sm text-blue-800 dark:text-blue-300">
                <span class="font-semibold">Senior Investigator (Rank {{ $user->rank }})</span> — You can upload evidence, manage chain of custody, and run integrity checks on cases you are assigned to.
            </p>
        </div>

        @else
        <div class="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-5 py-3">
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                <span class="font-semibold">Standard User (Rank {{ $user->rank }})</span> — You have basic system access. Evidence management requires Rank 3 (Senior Investigator) or above.
            </p>
        </div>
        @endif

        {{-- ── Quick actions grid ───────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- Upload Evidence — Rank 3+ only --}}
            @if($user->rank >= 3)
            <a href="{{ route('evidence.create') }}"
               class="group flex items-start gap-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/60 transition-colors">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Upload Evidence</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Add a new file to the evidence vault</p>
                </div>
            </a>
            @else
            {{-- Locked card for rank 1-2 --}}
            <div class="flex items-start gap-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-dashed border-slate-300 dark:border-slate-700 p-5 opacity-60 cursor-not-allowed"
                 title="Requires Rank 3 (Senior Investigator)">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Upload Evidence</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Requires Rank 3 — Senior Investigator</p>
                </div>
            </div>
            @endif

            {{-- My Profile --}}
            <a href="{{ route('profile.edit') }}"
               class="group flex items-start gap-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/60 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">My Profile</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage account settings & 2FA</p>
                </div>
            </a>

            {{-- Admin Panel — rank 8+ only --}}
            @if($user->rank >= 8)
            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-start gap-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5 hover:border-purple-400 dark:hover:border-purple-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-900/60 transition-colors">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Admin Panel</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Users, roles & activity logs</p>
                </div>
            </a>
            @endif

        </div>

        {{-- ── Senior Investigator section (rank 3–7 only) ─────────────── --}}
        @if($user->rank >= 3 && $user->rank < 8)
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Investigator Tools</h3>
                </div>
                <a href="{{ route('evidence.create') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                    Upload Evidence →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-slate-700">
                <div class="px-5 py-4 text-center">
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ Auth::user()->uploadedEvidence()->count() }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Evidence Uploaded</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ Auth::user()->assignedEvidence()->count() }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">In My Custody</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ Auth::user()->custodyTransferred()->count() }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Transfers Made</p>
                </div>
            </div>
        </div>
        @endif

        {{-- ── What you can't do yet (rank 1-2) ───────────────────────── --}}
        @if($user->rank < 3)
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Your Access Level</h3>
            <div class="space-y-2">
                @foreach([
                    ['✅', 'Login, manage your profile, change password, enable 2FA'],
                    ['✅', 'View your account activity and session history'],
                    ['🔒', 'Upload or manage evidence (requires Rank 3 — Senior Investigator)'],
                    ['🔒', 'Chain of custody transfers (requires Rank 3)'],
                    ['🔒', 'Hash integrity verification (requires Rank 3)'],
                    ['🔒', 'User management and admin panel (requires Rank 8)'],
                ] as [$icon, $label])
                <div class="flex items-center gap-2.5 text-sm {{ str_starts_with($icon, '🔒') ? 'text-slate-400 dark:text-slate-500' : 'text-slate-700 dark:text-slate-300' }}">
                    <span class="text-base leading-none">{{ $icon }}</span>
                    <span>{{ $label }}</span>
                </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">
                Contact your administrator to request a rank upgrade.
            </p>
        </div>
        @endif

        {{-- ── Account info strip ───────────────────────────────────────── --}}
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm px-5 py-4 flex flex-wrap items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
            <span>Rank <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $user->rank }}</span></span>
            <span class="text-slate-300 dark:text-slate-600">|</span>
            <span>{{ $user->email }}</span>
            <span class="text-slate-300 dark:text-slate-600">|</span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-400' }}"></span>
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
            @if($user->hasTwoFactorEnabled())
            <span class="text-slate-300 dark:text-slate-600">|</span>
            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                2FA On
            </span>
            @endif
        </div>

    </div>
</x-app-layout>
