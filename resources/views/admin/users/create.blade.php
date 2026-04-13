<x-admin-layout title="Create User">
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Create New User</h1>
            <p class="text-sm text-slate-500 mt-0.5">Add a new user to the system</p>
        </div>
    </div>

    <div class="card" x-data="{
        showPass: false,
        password: '',
        get strength() {
            let s = 0;
            if (this.password.length >= 12) s++;
            if (/[A-Z]/.test(this.password)) s++;
            if (/[0-9]/.test(this.password)) s++;
            if (/[\W_]/.test(this.password)) s++;
            return s;
        },
        get strengthLabel() { return ['Too short','Weak','Fair','Good','Strong'][this.strength]; },
        get strengthColor() { return ['bg-slate-200','bg-red-500','bg-amber-500','bg-yellow-400','bg-emerald-500'][this.strength]; },
        rankDescriptions: {
            1: 'Basic access — read-only on most resources',
            2: 'Limited access — standard user operations',
            3: 'Standard user with some elevated permissions',
            4: 'Intermediate — can manage own cases',
            5: 'Senior user — team lead capabilities',
            6: 'Supervisor — can oversee team members',
            7: 'Manager — department-level access',
            8: 'Admin — full user management access',
            9: 'Senior Admin — role and permission management',
            10: 'Super Admin — unrestricted system access'
        },
        selectedRank: {{ old('rank', 1) }}
    }">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">User Details</h2>
            <span class="badge badge-blue">New Account</span>
        </div>
        <div class="card-body space-y-5">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                               class="form-input {{ $errors->has('name') ? 'form-input-error' : '' }}"
                               placeholder="John Smith"/>
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                               class="form-input {{ $errors->has('email') ? 'form-input-error' : '' }}"
                               placeholder="user@agency.gov"/>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="password" class="form-label">Temporary Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="password" :type="showPass ? 'text' : 'password'" name="password"
                               x-model="password" required
                               class="form-input pr-10 {{ $errors->has('password') ? 'form-input-error' : '' }}"
                               placeholder="Min 12 chars, mixed case, numbers, symbols"/>
                        <button type="button" @click="showPass=!showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <div class="mt-2 space-y-1" x-show="password.length > 0">
                        <div class="flex gap-1">
                            <template x-for="i in 4">
                                <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="i <= strength ? strengthColor : 'bg-slate-200 dark:bg-slate-600'"></div>
                            </template>
                        </div>
                        <p class="text-xs" :class="{ 'text-red-500': strength<=1, 'text-amber-500': strength===2, 'text-yellow-500': strength===3, 'text-emerald-500': strength===4 }" x-text="strengthLabel"></p>
                    </div>
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="rank" class="form-label">
                            Rank
                            <span class="ml-1 text-xs text-slate-400">(1–{{ auth()->user()->rank }})</span>
                        </label>
                        <select id="rank" name="rank" x-model="selectedRank" required class="form-input {{ $errors->has('rank') ? 'form-input-error' : '' }}">
                            @foreach(range(1, auth()->user()->rank) as $r)
                            <option value="{{ $r }}" {{ old('rank',1)==$r ? 'selected' : '' }}>Rank {{ $r }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint" x-text="rankDescriptions[selectedRank]"></p>
                        @error('rank')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                        <select id="role" name="role" required class="form-input {{ $errors->has('role') ? 'form-input-error' : '' }}">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role')===$role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-700/40 rounded-lg">
                    <div x-data="{ active: {{ old('is_active', true) ? 'true' : 'false' }} }">
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
                        <p class="text-xs text-slate-400">User can log in immediately after creation</p>
                    </div>
                </div>

                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <strong>Note:</strong> A temporary password will be shown after creation. The user must change it on first login.
                    </p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>

