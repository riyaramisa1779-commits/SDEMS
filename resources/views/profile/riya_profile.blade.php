<x-app-layout>
<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Senior Investigator Profile
        </h2>
        {{-- Riya Special Badge --}}
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-md ring-2 ring-violet-300 dark:ring-violet-700">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Senior Investigator Profile &mdash; Riya
            </span>
        </div>
    </div>
</x-slot>

<div class="py-8">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

{{-- ═══════════════════════════════════════════════════════════════════════
     RIYA SPECIAL HERO BANNER
     Displayed only on this dedicated page to distinguish it from the
     standard /profile page.
════════════════════════════════════════════════════════════════════════ --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-indigo-600 to-blue-600 p-6 shadow-xl">
    {{-- Decorative background circles --}}
    <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full bg-white/10"></div>
    <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/10"></div>

    <div class="relative flex flex-col sm:flex-row items-start sm:items-center gap-4">
        {{-- Avatar --}}
        <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-black text-2xl shadow-lg ring-2 ring-white/30 flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <h1 class="text-xl font-bold text-white">{{ $user->name }}</h1>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white ring-1 ring-white/30">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Senior Investigator
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white ring-1 ring-white/30">
                    Rank {{ $user->rank }}
                </span>
            </div>
            <p class="text-violet-100 text-sm truncate">{{ $user->email }}</p>
            <p class="text-violet-200 text-xs mt-1">Dedicated profile &bull; Full access to evidence management &amp; custody transfers</p>
        </div>

        {{-- 2FA shield --}}
        @if($user->hasTwoFactorEnabled())
        <div class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/30 ring-1 ring-emerald-400/50 text-emerald-100 text-xs font-semibold">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            2FA Active
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     STATUS ALERTS
     Same flash messages as the standard profile page.
════════════════════════════════════════════════════════════════════════ --}}
@foreach([
    ['profile-updated',  'green',  'Profile information updated successfully.'],
    ['password-updated', 'green',  'Password changed successfully.'],
    ['2fa-setup',        'blue',   'Scan the QR code with your authenticator app, then enter the 6-digit code.'],
    ['2fa-enabled',      'green',  'Two-factor authentication enabled successfully.'],
    ['2fa-disabled',     'yellow', 'Two-factor authentication has been disabled.'],
    ['session-revoked',  'green',  'Session revoked successfully.'],
] as [$key, $color, $msg])
@if(session('status') === $key)
<div x-data="{ show: true }" x-show="show"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="alert-{{ $color }} animate-fade-in">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p>{{ $msg }}</p>
    <button @click="show = false" class="ml-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif
@endforeach

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ═══════════════════════════════════════════════════════════════════
         LEFT COLUMN — Avatar card + Account info card
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-5">

        {{-- Avatar / Identity card --}}
        <div class="card p-6 text-center">
            <div class="relative inline-block mb-4">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white font-bold text-3xl mx-auto shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                {{-- Senior Investigator crown icon --}}
                <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-amber-400 flex items-center justify-center shadow-md ring-2 ring-white dark:ring-slate-800">
                    <svg class="w-4 h-4 text-amber-900" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>

            <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg">{{ $user->name }}</h2>
            <p class="text-sm text-slate-400 mt-0.5">{{ $user->email }}</p>

            <div class="flex items-center justify-center gap-2 mt-3 flex-wrap">
                <x-rank-badge :rank="$user->rank"/>
                <span class="badge badge-gray">{{ $user->getRoleNames()->first() ?? 'No Role' }}</span>
            </div>

            {{-- Senior Investigator label --}}
            <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 ring-1 ring-violet-200 dark:ring-violet-700">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Senior Investigator
            </div>

            @if($user->hasTwoFactorEnabled())
            <div class="mt-3 flex items-center justify-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                2FA Protected
            </div>
            @endif
        </div>

        {{-- Account Info card --}}
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Account Info</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Member since</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">
                        {{ $user->created_at->format('d M Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Password expires</span>
                    <span class="font-medium {{ $user->passwordExpiresInDays() !== null && $user->passwordExpiresInDays() <= 14 ? 'text-red-500' : 'text-slate-700 dark:text-slate-300' }}">
                        @if($user->passwordExpiresInDays() !== null)
                            {{ $user->passwordExpiresInDays() }} days
                        @else
                            Never
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Email verified</span>
                    <span class="font-medium {{ $user->email_verified_at ? 'text-emerald-600' : 'text-amber-500' }}">
                        {{ $user->email_verified_at ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Rank level</span>
                    <span class="font-medium text-violet-600 dark:text-violet-400">{{ $user->rank }} / 10</span>
                </div>
            </div>
        </div>

        {{-- Quick-link back to standard profile --}}
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-2 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Standard Profile
        </a>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         RIGHT COLUMN — All forms (identical functionality to /profile)
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- ── 1. Profile Information Form ─────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Profile Information</h3>
                <span class="badge badge-gray text-xs">Rank {{ $user->rank }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   class="form-input"/>
                            @error('name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   class="form-input"/>
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Rank</label>
                            <input type="text"
                                   value="Rank {{ $user->rank }} — Senior Investigator"
                                   disabled
                                   class="form-input bg-slate-50 dark:bg-slate-700/50 text-slate-500 cursor-not-allowed"/>
                        </div>
                        <div>
                            <label class="form-label">Role</label>
                            <input type="text"
                                   value="{{ $user->getRoleNames()->first() ?? 'No Role Assigned' }}"
                                   disabled
                                   class="form-input bg-slate-50 dark:bg-slate-700/50 text-slate-500 cursor-not-allowed"/>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Save Profile</button>
                </form>
            </div>
        </div>

        {{-- ── 2. Change Password Form (with Alpine.js strength meter) ─── --}}
        <div class="card"
             x-data="{
                 showCurrent: false,
                 showNew: false,
                 showConfirm: false,
                 password: '',
                 get strength() {
                     let s = 0;
                     if (this.password.length >= 12) s++;
                     if (/[A-Z]/.test(this.password)) s++;
                     if (/[0-9]/.test(this.password)) s++;
                     if (/[\W_]/.test(this.password)) s++;
                     return s;
                 },
                 get strengthLabel() {
                     return ['', 'Weak', 'Fair', 'Good', 'Strong'][this.strength];
                 },
                 get strengthColor() {
                     return ['bg-slate-200 dark:bg-slate-600', 'bg-red-500', 'bg-amber-500', 'bg-yellow-400', 'bg-emerald-500'][this.strength];
                 },
                 get strengthTextColor() {
                     return ['text-slate-400', 'text-red-500', 'text-amber-500', 'text-yellow-500', 'text-emerald-500'][this.strength];
                 }
             }">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Change Password</h3>
                @if($user->passwordExpiresInDays() !== null && $user->passwordExpiresInDays() <= 14)
                    <span class="badge badge-red">Expires in {{ $user->passwordExpiresInDays() }} days</span>
                @endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Current Password --}}
                    <div>
                        <label class="form-label">Current Password</label>
                        <div class="relative">
                            <input :type="showCurrent ? 'text' : 'password'"
                                   name="current_password"
                                   required
                                   class="form-input pr-10 {{ $errors->has('current_password') ? 'form-input-error' : '' }}"/>
                            <button type="button"
                                    @click="showCurrent = !showCurrent"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password + Strength Meter --}}
                    <div>
                        <label class="form-label">New Password</label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'"
                                   name="password"
                                   x-model="password"
                                   required
                                   placeholder="Min 12 chars, mixed case, numbers, symbols"
                                   class="form-input pr-10 {{ $errors->has('password') ? 'form-input-error' : '' }}"/>
                            <button type="button"
                                    @click="showNew = !showNew"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Strength meter bars + label --}}
                        <div class="mt-2 space-y-1" x-show="password.length > 0" x-transition>
                            <div class="flex gap-1">
                                <template x-for="i in 4" :key="i">
                                    <div class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                                         :class="i <= strength ? strengthColor : 'bg-slate-200 dark:bg-slate-600'">
                                    </div>
                                </template>
                            </div>
                            <p class="text-xs font-medium" :class="strengthTextColor" x-text="strengthLabel"></p>
                        </div>

                        {{-- Policy hints --}}
                        <div class="mt-2 space-y-1" x-show="password.length > 0" x-transition>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                <span :class="password.length >= 12 ? 'text-emerald-600 dark:text-emerald-400' : ''">
                                    <span x-text="password.length >= 12 ? '✓' : '○'"></span> 12+ chars
                                </span>
                                <span :class="/[A-Z]/.test(password) ? 'text-emerald-600 dark:text-emerald-400' : ''">
                                    <span x-text="/[A-Z]/.test(password) ? '✓' : '○'"></span> Uppercase
                                </span>
                                <span :class="/[0-9]/.test(password) ? 'text-emerald-600 dark:text-emerald-400' : ''">
                                    <span x-text="/[0-9]/.test(password) ? '✓' : '○'"></span> Number
                                </span>
                                <span :class="/[\W_]/.test(password) ? 'text-emerald-600 dark:text-emerald-400' : ''">
                                    <span x-text="/[\W_]/.test(password) ? '✓' : '○'"></span> Symbol
                                </span>
                            </div>
                        </div>

                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'"
                                   name="password_confirmation"
                                   required
                                   class="form-input pr-10"/>
                            <button type="button"
                                    @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary btn-sm">Update Password</button>
                </form>
            </div>
        </div>

        {{-- ── 3. Two-Factor Authentication ────────────────────────────── --}}
        <div class="card" x-data="{ showDisable: false }">
            <div class="card-header">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Two-Factor Authentication</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Extra security via authenticator app (Google Authenticator, Authy)</p>
                </div>
                @if($user->hasTwoFactorEnabled())
                    <span class="badge badge-green">Enabled</span>
                @else
                    <span class="badge badge-gray">Disabled</span>
                @endif
            </div>
            <div class="card-body">

                @if($user->hasTwoFactorEnabled())
                    {{-- 2FA is active --}}
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg mb-4">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300">
                            Your account is protected with two-factor authentication.
                        </p>
                    </div>

                    @if($recoveryCodes->isNotEmpty())
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                            Recovery Codes &mdash; store these safely:
                        </p>
                        <div class="bg-slate-900 dark:bg-slate-950 rounded-lg p-4 grid grid-cols-2 gap-1.5">
                            @foreach($recoveryCodes as $code)
                                <code class="text-xs font-mono text-emerald-400">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <button @click="showDisable = !showDisable" class="btn-danger btn-sm">Disable 2FA</button>

                    <div x-show="showDisable" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-4">
                        <form method="POST" action="{{ route('profile.two-factor.disable') }}" class="flex gap-3 items-end">
                            @csrf
                            @method('DELETE')
                            <div>
                                <label class="form-label">Confirm your password</label>
                                <input type="password" name="password" required class="form-input w-48"/>
                            </div>
                            <button type="submit" class="btn-danger btn-sm">Confirm Disable</button>
                        </form>
                    </div>

                @elseif($user->two_factor_secret && !$user->hasTwoFactorEnabled())
                    {{-- 2FA secret generated, awaiting confirmation --}}
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                        Scan this QR code with your authenticator app, then enter the 6-digit code to confirm setup.
                    </p>
                    <div class="inline-block border-4 border-white rounded-xl shadow-md mb-4 bg-white">
                        {!! $qrCodeSvg !!}
                    </div>
                    <form method="POST" action="{{ route('profile.two-factor.confirm') }}" class="flex gap-3 items-end">
                        @csrf
                        <div>
                            <label class="form-label">6-digit verification code</label>
                            <input type="text" name="code"
                                   maxlength="6" pattern="\d{6}" required
                                   class="form-input w-36 text-center text-lg tracking-widest font-mono {{ $errors->has('code') ? 'form-input-error' : '' }}"
                                   placeholder="000000"/>
                            @error('code')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn-primary btn-sm">Verify &amp; Enable</button>
                    </form>

                @else
                    {{-- 2FA not set up --}}
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                        Two-factor authentication adds an extra layer of security to your account.
                        As a Senior Investigator, enabling 2FA is strongly recommended.
                    </p>
                    <form method="POST" action="{{ route('profile.two-factor.enable') }}">
                        @csrf
                        <button type="submit" class="btn-primary btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Enable 2FA
                        </button>
                    </form>
                @endif

            </div>
        </div>

        {{-- ── 4. Active Sessions ───────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Active Sessions</h3>
                <span class="badge badge-blue">{{ $sessions->count() }} device(s)</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($sessions as $device)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ $device->device_name }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ $device->ip_address }} &bull; {{ $device->last_active_at?->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @if($device->session_id === session()->getId())
                        <span class="badge badge-green">Current</span>
                    @else
                        <form method="POST" action="{{ route('profile.sessions.revoke', $device->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">Revoke</button>
                        </form>
                    @endif
                </div>
                @empty
                <div class="px-6 py-8 text-center text-sm text-slate-400">
                    No active sessions tracked.
                </div>
                @endforelse
            </div>
        </div>

        {{-- ── 5. Danger Zone — Delete Account ─────────────────────────── --}}
        <div class="card border-red-200 dark:border-red-900/50" x-data="{ show: false }">
            <div class="card-header border-red-100 dark:border-red-900/30">
                <h3 class="text-sm font-semibold text-red-700 dark:text-red-400">Danger Zone</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    Deleting your account will soft-delete it. An administrator can restore it if needed.
                </p>
                <button @click="show = !show" class="btn-danger btn-sm">Delete My Account</button>
                <div x-show="show" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-4">
                    <form method="POST" action="{{ route('profile.destroy') }}" class="flex gap-3 items-end">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label class="form-label">Confirm your password</label>
                            <input type="password" name="password" required
                                   class="form-input w-48 {{ $errors->has('userDeletion.password') ? 'form-input-error' : '' }}"/>
                            @error('userDeletion.password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn-danger btn-sm">Confirm Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- end right column --}}
</div>{{-- end grid --}}
</div>{{-- end inner container --}}
</div>{{-- end py-8 --}}
</x-app-layout>
