<x-admin-layout title="Edit User">
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Edit User</h1>
            <p class="text-sm text-slate-500 mt-0.5">Modifying: {{ $user->name }}</p>
        </div>
    </div>

    {{-- User info banner --}}
    <div class="card mb-5 p-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full {{ $user->rank >= 8 ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : 'bg-gradient-to-br from-slate-400 to-slate-500' }} flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr($user->name,0,1)) }}
            </div>
            <div class="flex-1">
                <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $user->name }}</p>
                <p class="text-sm text-slate-400">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <x-rank-badge :rank="$user->rank"/>
                    @if($user->is_active) <span class="badge badge-green">Active</span>
                    @else <span class="badge badge-gray">Inactive</span> @endif
                    @if($user->hasTwoFactorEnabled()) <span class="badge badge-blue">2FA On</span> @endif
                </div>
            </div>
            <div class="text-right text-xs text-slate-400 space-y-1">
                <p>Joined: {{ $user->created_at->format('d M Y') }}</p>
                <p>Last pw change: {{ $user->password_changed_at?->format('d M Y') ?? 'Never' }}</p>
                <p>Failed logins: {{ $user->failed_login_attempts }}</p>
                @if($user->isLocked())
                <p class="text-red-500 font-medium">🔒 Locked until {{ $user->locked_until->format('H:i d M') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="card" x-data="{
        rankDescriptions: {
            1:'Basic access', 2:'Limited access', 3:'Standard user', 4:'Intermediate',
            5:'Senior user', 6:'Supervisor', 7:'Manager', 8:'Admin', 9:'Senior Admin', 10:'Super Admin'
        },
        selectedRank: {{ old('rank', $user->rank) }}
    }">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Edit Details</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update',$user) }}" class="space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name',$user->name) }}" required
                               class="form-input {{ $errors->has('name') ? 'form-input-error' : '' }}"/>
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email',$user->email) }}" required
                               class="form-input {{ $errors->has('email') ? 'form-input-error' : '' }}"/>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Rank <span class="text-xs text-slate-400">(max: {{ auth()->user()->rank }})</span></label>
                        <select name="rank" x-model="selectedRank" required class="form-input {{ $errors->has('rank') ? 'form-input-error' : '' }}">
                            @foreach(range(1, auth()->user()->rank) as $r)
                            <option value="{{ $r }}" {{ old('rank',$user->rank)==$r ? 'selected' : '' }}>Rank {{ $r }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint" x-text="rankDescriptions[selectedRank]"></p>
                        @error('rank')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="form-input {{ $errors->has('role') ? 'form-input-error' : '' }}">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role',$user->getRoleNames()->first())===$role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-700/40 rounded-lg">
                    <div x-data="{ active: {{ $user->is_active ? 'true' : 'false' }} }">
                        <button type="button" @click="active=!active"
                                :class="active ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <span :class="active ? 'translate-x-6' : 'translate-x-1'"
                                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                        </button>
                        <input type="hidden" name="is_active" :value="active ? 1 : 0"/>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Active Account</p>
                        <p class="text-xs text-slate-400">Toggle to activate or deactivate this user</p>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>

