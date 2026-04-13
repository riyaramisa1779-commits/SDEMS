<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Create account</h2>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">Register for SDEMS access</p>
    </div>
    <form method="POST" action="{{ route('register') }}" class="space-y-5"
          x-data="{ showPass: false, password: '', get strength() { let s=0; if(this.password.length>=12)s++; if(/[A-Z]/.test(this.password))s++; if(/[0-9]/.test(this.password))s++; if(/[\W_]/.test(this.password))s++; return s; }, get strengthLabel() { return ['Too short','Weak','Fair','Good','Strong'][this.strength]; }, get strengthColor() { return ['bg-slate-200','bg-red-500','bg-amber-500','bg-yellow-400','bg-emerald-500'][this.strength]; } }">
        @csrf
        <div>
            <label for="name" class="form-label">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="form-input @error('name') form-input-error @enderror" placeholder="John Smith"/>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="form-input @error('email') form-input-error @enderror" placeholder="you@agency.gov"/>
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="form-label">Password</label>
            <div class="relative">
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" x-model="password" required autocomplete="new-password"
                       class="form-input pr-10 @error('password') form-input-error @enderror" placeholder="Min 12 chars, mixed case, numbers, symbols"/>
                <button type="button" @click="showPass=!showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
            <div class="mt-2 space-y-1" x-show="password.length > 0">
                <div class="flex gap-1">
                    <template x-for="i in 4"><div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="i <= strength ? strengthColor : 'bg-slate-200 dark:bg-slate-600'"></div></template>
                </div>
                <p class="text-xs" :class="{ 'text-red-500': strength<=1, 'text-amber-500': strength===2, 'text-yellow-500': strength===3, 'text-emerald-500': strength===4 }" x-text="strengthLabel"></p>
            </div>
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input id="password_confirmation" :type="showPass ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                   class="form-input" placeholder="Repeat password"/>
            @error('password_confirmation')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-2.5">Create Account</button>
        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            Already have an account? <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">Sign in</a>
        </p>
    </form>
</x-guest-layout>
