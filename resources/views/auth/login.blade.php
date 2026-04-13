<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Welcome back</h2>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">Sign in to your SDEMS account</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPass: false }">
        @csrf

        <div>
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="form-input @error('email') form-input-error @enderror"
                   placeholder="you@agency.gov"/>
            @error('email')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="form-label mb-0">Password</label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password"
                       required autocomplete="current-password"
                       class="form-input pr-10 @error('password') form-input-error @enderror"
                       placeholder="••••••••••••"/>
                <button type="button" @click="showPass=!showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            @error('password')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"/>
            <label for="remember_me" class="text-sm text-slate-600 dark:text-slate-400">Remember me</label>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            Sign In Securely
        </button>

        @if(Route::has('register'))
        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            Don't have an account? <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">Request access</a>
        </p>
        @endif
    </form>

    <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-700">
        <p class="text-center text-xs text-slate-400">🔒 Authorized personnel only. All access is logged and monitored.</p>
    </div>
</x-guest-layout>
