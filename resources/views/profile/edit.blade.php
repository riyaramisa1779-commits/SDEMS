<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">My Profile</h2>
</x-slot>

<div class="py-8">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

{{-- Status alerts --}}
@foreach([
    ['profile-updated',  'green',  'Profile information updated successfully.'],
    ['password-updated', 'green',  'Password changed successfully.'],
    ['2fa-setup',        'blue',   'Scan the QR code with your authenticator app, then enter the 6-digit code.'],
    ['2fa-enabled',      'green',  'Two-factor authentication enabled successfully.'],
    ['2fa-disabled',     'yellow', 'Two-factor authentication has been disabled.'],
    ['session-revoked',  'green',  'Session revoked successfully.'],
] as [$key, $color, $msg])
@if(session('status')===$key)
<div x-data="{show:true}" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="alert-{{ $color }} animate-fade-in">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p>{{ $msg }}</p>
    <button @click="show=false" class="ml-auto"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
</div>
@endif
@endforeach

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Avatar + Info --}}
    <div class="space-y-5">
        <div class="card p-6 text-center">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-3xl mx-auto mb-4">
                {{ strtoupper(substr($user->name,0,1)) }}
            </div>
            <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg">{{ $user->name }}</h2>
            <p class="text-sm text-slate-400 mt-0.5">{{ $user->email }}</p>
            <div class="flex items-center justify-center gap-2 mt-3">
                <x-rank-badge :rank="$user->rank"/>
                <span class="badge badge-gray">{{ $user->getRoleNames()->first() }}</span>
            </div>
            @if($user->hasTwoFactorEnabled())
            <div class="mt-3 flex items-center justify-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                2FA Protected
            </div>
            @endif
        </div>

        <div class="card p-5">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Account Info</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Member since</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Password expires</span>
                    <span class="font-medium {{ $user->passwordExpiresInDays() !== null && $user->passwordExpiresInDays() <= 14 ? 'text-red-500' : 'text-slate-700 dark:text-slate-300' }}">
                        @if($user->passwordExpiresInDays() !== null)
                            {{ $user->passwordExpiresInDays() }} days
                        @else Never @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Email verified</span>
                    <span class="font-medium {{ $user->email_verified_at ? 'text-emerald-600' : 'text-amber-500' }}">
                        {{ $user->email_verified_at ? 'Yes' : 'No' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Forms --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Profile info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Profile Information</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" value="{{ old('name',$user->name) }}" required class="form-input"/>
                            @error('name')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email',$user->email) }}" required class="form-input"/>
                            @error('email')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Save Profile</button>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card" x-data="{ showCurrent: false, showNew: false, password: '', get strength() { let s=0; if(this.password.length>=12)s++; if(/[A-Z]/.test(this.password))s++; if(/[0-9]/.test(this.password))s++; if(/[\W_]/.test(this.password))s++; return s; }, get strengthColor() { return ['bg-slate-200','bg-red-500','bg-amber-500','bg-yellow-400','bg-emerald-500'][this.strength]; } }">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Change Password</h3>
                @if($user->passwordExpiresInDays() !== null && $user->passwordExpiresInDays() <= 14)
                <span class="badge badge-red">Expires in {{ $user->passwordExpiresInDays() }} days</span>
                @endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="form-label">Current Password</label>
                        <div class="relative">
                            <input :type="showCurrent ? 'text' : 'password'" name="current_password" required class="form-input pr-10 {{ $errors->has('current_password') ? 'form-input-error' : '' }}"/>
                            <button type="button" @click="showCurrent=!showCurrent" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">New Password</label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'" name="password" x-model="password" required class="form-input pr-10 {{ $errors->has('password') ? 'form-input-error' : '' }}" placeholder="Min 12 chars, mixed case, numbers, symbols"/>
                            <button type="button" @click="showNew=!showNew" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <div class="mt-2 space-y-1" x-show="password.length > 0">
                            <div class="flex gap-1">
                                <template x-for="i in 4">
                                    <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="i <= strength ? strengthColor : 'bg-slate-200 dark:bg-slate-600'"></div>
                                </template>
                            </div>
                        </div>
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input :type="showNew ? 'text' : 'password'" name="password_confirmation" required class="form-input"/>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Update Password</button>
                </form>
            </div>
        </div>

        {{-- 2FA --}}
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
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg mb-4">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300">Your account is protected with two-factor authentication.</p>
                    </div>
                    @if($recoveryCodes->isNotEmpty())
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Recovery Codes — store these safely:</p>
                        <div class="bg-slate-900 dark:bg-slate-950 rounded-lg p-4 grid grid-cols-2 gap-1.5">
                            @foreach($recoveryCodes as $code)
                            <code class="text-xs font-mono text-emerald-400">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <button @click="showDisable=!showDisable" class="btn-danger btn-sm">Disable 2FA</button>
                    <div x-show="showDisable" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4">
                        <form method="POST" action="{{ route('profile.two-factor.disable') }}" class="flex gap-3 items-end">
                            @csrf @method('DELETE')
                            <div>
                                <label class="form-label">Confirm your password</label>
                                <input type="password" name="password" required class="form-input w-48"/>
                            </div>
                            <button type="submit" class="btn-danger btn-sm">Confirm Disable</button>
                        </form>
                    </div>
                @elseif($user->two_factor_secret && !$user->hasTwoFactorEnabled())
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Scan this QR code with your authenticator app, then enter the 6-digit code to confirm setup.</p>
                    <div class="inline-block border-4 border-white rounded-xl shadow-md mb-4 bg-white">
                        {!! $qrCodeSvg !!}
                    </div>
                    <form method="POST" action="{{ route('profile.two-factor.confirm') }}" class="flex gap-3 items-end">
                        @csrf
                        <div>
                            <label class="form-label">6-digit verification code</label>
                            <input type="text" name="code" maxlength="6" pattern="\d{6}" required
                                   class="form-input w-36 text-center text-lg tracking-widest font-mono {{ $errors->has('code') ? 'form-input-error' : '' }}" placeholder="000000"/>
                            @error('code')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn-primary btn-sm">Verify & Enable</button>
                    </form>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Two-factor authentication adds an extra layer of security to your account.</p>
                    <form method="POST" action="{{ route('profile.two-factor.enable') }}">
                        @csrf
                        <button type="submit" class="btn-primary btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Enable 2FA
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Active Sessions --}}
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
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $device->device_name }}</p>
                            <p class="text-xs text-slate-400">{{ $device->ip_address }} · {{ $device->last_active_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if($device->session_id === session()->getId())
                        <span class="badge badge-green">Current</span>
                    @else
                        <form method="POST" action="{{ route('profile.sessions.revoke',$device->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">Revoke</button>
                        </form>
                    @endif
                </div>
                @empty
                <div class="px-6 py-8 text-center text-sm text-slate-400">No active sessions tracked.</div>
                @endforelse
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="card border-red-200 dark:border-red-900/50" x-data="{ show: false }">
            <div class="card-header border-red-100 dark:border-red-900/30">
                <h3 class="text-sm font-semibold text-red-700 dark:text-red-400">Danger Zone</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    Deleting your account will soft-delete it. An administrator can restore it if needed.
                </p>
                <button @click="show=!show" class="btn-danger btn-sm">Delete My Account</button>
                <div x-show="show" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4">
                    <form method="POST" action="{{ route('profile.destroy') }}" class="flex gap-3 items-end">
                        @csrf @method('DELETE')
                        <div>
                            <label class="form-label">Confirm your password</label>
                            <input type="password" name="password" required class="form-input w-48 {{ $errors->has('userDeletion.password') ? 'form-input-error' : '' }}"/>
                            @error('userDeletion.password')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn-danger btn-sm">Confirm Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
</div>
</x-app-layout>

