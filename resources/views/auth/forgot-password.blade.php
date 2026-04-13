<x-guest-layout>
    <div class="mb-8">
        <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Reset password</h2>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">Enter your email and we'll send a secure reset link.</p>
    </div>
    @if(session('status'))
    <div class="alert-success mb-5">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p>{{ session('status') }}</p>
    </div>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="form-input @error('email') form-input-error @enderror" placeholder="you@agency.gov"/>
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-2.5">Send Reset Link</button>
        <p class="text-center text-sm text-slate-500"><a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">← Back to sign in</a></p>
    </form>
</x-guest-layout>
