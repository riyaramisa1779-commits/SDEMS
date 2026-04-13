<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode')==='true', mobileMenu: false }"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome To Secure Digital Evidence Management System</title>
    <meta name="description" content="SDEMS — A secure, role-based digital evidence management platform for law enforcement and legal professionals.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300">

{{-- ═══════════════════════════════════════════════
     NAVBAR
═══════════════════════════════════════════════ --}}
<nav class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md border-b border-slate-200/60 dark:border-slate-800/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="hidden sm:block">
                    <p class="font-bold text-slate-900 dark:text-white text-sm leading-none">SDEMS</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-none mt-0.5">Evidence Management</p>
                </div>
            </div>

            {{-- Desktop nav links --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="#features" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">Features</a>
                <a href="#security" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">Security</a>
                <a href="#about" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">About</a>
            </div>

            {{-- Right actions --}}
            <div class="flex items-center gap-2">
                {{-- Dark mode toggle --}}
                <button @click="darkMode=!darkMode; localStorage.setItem('darkMode', darkMode)"
                        class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        Sign In
                    </a>
                    @if(Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm shadow-indigo-500/30">
                        Get Access
                    </a>
                    @endif
                @endauth

                {{-- Mobile menu button --}}
                <button @click="mobileMenu=!mobileMenu" class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenu}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !mobileMenu}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileMenu" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 px-4 py-3 space-y-1">
        <a href="#features" @click="mobileMenu=false" class="block px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">Features</a>
        <a href="#security" @click="mobileMenu=false" class="block px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">Security</a>
        <a href="#about" @click="mobileMenu=false" class="block px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">About</a>
        @auth
            <a href="{{ url('/dashboard') }}" class="block px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400">Dashboard →</a>
        @else
            <a href="{{ route('login') }}" class="block px-3 py-2 text-sm text-slate-700 dark:text-slate-300">Sign In</a>
        @endauth
    </div>
</nav>

{{-- ═══════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════ --}}
<section class="relative min-h-screen flex items-center pt-16 overflow-hidden">

    {{-- Background gradient --}}
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-indigo-50/30 to-slate-50 dark:from-slate-950 dark:via-indigo-950/20 dark:to-slate-950"></div>

    {{-- Grid pattern --}}
    <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"
         style="background-image: linear-gradient(#6366f1 1px, transparent 1px), linear-gradient(to right, #6366f1 1px, transparent 1px); background-size: 48px 48px;"></div>

    {{-- Glow orbs --}}
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-400/10 dark:bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-purple-400/10 dark:bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Left: Text content --}}
            <div class="text-center lg:text-left">

                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-xs font-semibold mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    Authorized Personnel Only
                </div>

                {{-- Headline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 dark:text-white leading-tight tracking-tight mb-6">
                    Welcome To
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                        Secure Digital
                    </span>
                    Evidence Management
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                        System
                    </span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0">
                    A comprehensive, role-based platform for managing digital evidence with complete audit trails, chain-of-custody tracking, and enterprise-grade security.
                </p>

                {{-- CTA buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all duration-150 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all duration-150 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Sign In to System
                        </a>
                        @if(Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 text-base font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-all duration-150 shadow-sm hover:-translate-y-0.5">
                            Request Access
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @endif
                    @endauth
                </div>

                {{-- Trust indicators --}}
                <div class="mt-10 flex flex-wrap items-center gap-4 justify-center lg:justify-start">
                    @foreach([
                        ['🔒', 'End-to-End Encrypted'],
                        ['📋', 'Full Audit Trail'],
                        ['🛡️', 'Role-Based Access'],
                    ] as [$icon, $label])
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span>{{ $icon }}</span>
                        <span>{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: Visual card --}}
            <div class="hidden lg:block">
                <div class="relative">
                    {{-- Main card --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-6 relative z-10">
                        {{-- Card header --}}
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm">SDEMS Dashboard</p>
                                    <p class="text-xs text-slate-400">Secure Evidence Portal</p>
                                </div>
                            </div>
                            <span class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                System Online
                            </span>
                        </div>

                        {{-- Stats row --}}
                        <div class="grid grid-cols-3 gap-3 mb-5">
                            @foreach([
                                ['Total Users', '24', 'indigo'],
                                ['Active Cases', '138', 'emerald'],
                                ['Audit Logs', '4.2K', 'purple'],
                            ] as [$label, $val, $color])
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $val }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $label }}</p>
                            </div>
                            @endforeach
                        </div>

                        {{-- Recent activity --}}
                        <div class="space-y-2.5">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Recent Activity</p>
                            @foreach([
                                ['User logged in', 'auth', '2m ago', 'emerald'],
                                ['Evidence uploaded', 'evidence', '15m ago', 'blue'],
                                ['Role updated', 'user_management', '1h ago', 'purple'],
                                ['Report generated', 'report', '3h ago', 'amber'],
                            ] as [$desc, $type, $time, $color])
                            <div class="flex items-center gap-3 py-2 border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                                <div class="w-7 h-7 rounded-lg bg-{{ $color }}-100 dark:bg-{{ $color }}-900/40 flex items-center justify-center flex-shrink-0">
                                    <div class="w-2 h-2 rounded-full bg-{{ $color }}-500"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $desc }}</p>
                                    <p class="text-xs text-slate-400">{{ $type }}</p>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap">{{ $time }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Floating badge --}}
                    <div class="absolute -top-4 -right-4 bg-emerald-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg shadow-emerald-500/30 z-20">
                        ✓ Secure & Encrypted
                    </div>

                    {{-- Background card shadow --}}
                    <div class="absolute inset-0 bg-indigo-600/10 dark:bg-indigo-600/20 rounded-2xl translate-x-3 translate-y-3 -z-10"></div>
                </div>
            </div>

        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 text-slate-400 dark:text-slate-600 animate-bounce">
        <span class="text-xs">Scroll</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     FEATURES SECTION
═══════════════════════════════════════════════ --}}
<section id="features" class="py-20 lg:py-28 bg-slate-50 dark:bg-slate-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="text-center mb-14">
            <span class="inline-block px-3 py-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/40 rounded-full mb-3">Platform Features</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-4">
                Everything you need to manage<br class="hidden sm:block"> digital evidence securely
            </h2>
            <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
                Built for law enforcement, legal teams, and security professionals who need a reliable, auditable evidence management platform.
            </p>
        </div>

        {{-- Features grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                [
                    'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                    'color' => 'indigo',
                    'title' => 'Role-Based Access Control',
                    'desc' => 'Granular permissions with a 10-level rank hierarchy. Every user gets exactly the access they need — nothing more.',
                ],
                [
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'color' => 'emerald',
                    'title' => 'Complete Audit Trail',
                    'desc' => 'Every action is logged with timestamp, user identity, and context. Full chain-of-custody for every piece of evidence.',
                ],
                [
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'color' => 'blue',
                    'title' => 'Two-Factor Authentication',
                    'desc' => 'Mandatory 2FA support with TOTP authenticator apps. Recovery codes and session management built in.',
                ],
                [
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'color' => 'purple',
                    'title' => 'User Management',
                    'desc' => 'Full lifecycle management — create, edit, deactivate, and restore users. Bulk CSV import/export with rank enforcement.',
                ],
                [
                    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    'color' => 'amber',
                    'title' => 'Account Security',
                    'desc' => 'Automatic lockout after failed attempts, password expiry enforcement, history tracking, and session revocation.',
                ],
                [
                    'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4 8 4m0 5c-4.418 0-8-1.79-8-4',
                    'color' => 'rose',
                    'title' => 'Secure Evidence Storage',
                    'desc' => 'Encrypted storage with integrity verification. Tamper-evident records ensure evidence admissibility in legal proceedings.',
                ],
            ] as $feature)
            <div class="group bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:border-{{ $feature['color'] }}-300 dark:hover:border-{{ $feature['color'] }}-700 hover:shadow-lg hover:shadow-{{ $feature['color'] }}-500/10 transition-all duration-200">
                <div class="w-12 h-12 rounded-xl bg-{{ $feature['color'] }}-100 dark:bg-{{ $feature['color'] }}-900/40 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-6 h-6 text-{{ $feature['color'] }}-600 dark:text-{{ $feature['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-800 dark:text-slate-100 mb-2">{{ $feature['title'] }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECURITY SECTION
═══════════════════════════════════════════════ --}}
<section id="security" class="py-20 lg:py-28 bg-white dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Left: Security visual --}}
            <div class="relative">
                <div class="bg-slate-900 dark:bg-slate-800 rounded-2xl p-6 border border-slate-700 shadow-2xl font-mono text-sm">
                    {{-- Terminal header --}}
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-700">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span class="ml-2 text-xs text-slate-400">security-audit.log</span>
                    </div>
                    {{-- Log lines --}}
                    <div class="space-y-2 text-xs">
                        <div class="flex gap-3">
                            <span class="text-slate-500 whitespace-nowrap">{{ now()->format('H:i:s') }}</span>
                            <span class="text-emerald-400">[AUTH]</span>
                            <span class="text-slate-300">User login successful — rank:8 admin@sdems.local</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-slate-500 whitespace-nowrap">{{ now()->subMinutes(2)->format('H:i:s') }}</span>
                            <span class="text-blue-400">[ACCESS]</span>
                            <span class="text-slate-300">Evidence record accessed — case #2024-0891</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-slate-500 whitespace-nowrap">{{ now()->subMinutes(5)->format('H:i:s') }}</span>
                            <span class="text-amber-400">[RANK]</span>
                            <span class="text-slate-300">Rank access check passed — required:5 user:8</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-slate-500 whitespace-nowrap">{{ now()->subMinutes(8)->format('H:i:s') }}</span>
                            <span class="text-red-400">[WARN]</span>
                            <span class="text-slate-300">Failed login attempt — IP: 192.168.1.45 (3/5)</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-slate-500 whitespace-nowrap">{{ now()->subMinutes(12)->format('H:i:s') }}</span>
                            <span class="text-purple-400">[ROLE]</span>
                            <span class="text-slate-300">Permission granted — users.edit for admin role</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-slate-500 whitespace-nowrap">{{ now()->subMinutes(15)->format('H:i:s') }}</span>
                            <span class="text-emerald-400">[2FA]</span>
                            <span class="text-slate-300">Two-factor authentication verified successfully</span>
                        </div>
                        <div class="flex gap-3 opacity-50">
                            <span class="text-slate-500 whitespace-nowrap">{{ now()->subMinutes(20)->format('H:i:s') }}</span>
                            <span class="text-slate-400">[SYS]</span>
                            <span class="text-slate-400">Session tracking updated — device: Desktop Browser</span>
                        </div>
                    </div>
                    {{-- Blinking cursor --}}
                    <div class="mt-3 flex items-center gap-1">
                        <span class="text-emerald-400 text-xs">$</span>
                        <span class="w-2 h-4 bg-emerald-400 animate-pulse"></span>
                    </div>
                </div>
                {{-- Glow --}}
                <div class="absolute inset-0 bg-indigo-600/5 rounded-2xl blur-xl -z-10 scale-110"></div>
            </div>

            {{-- Right: Security text --}}
            <div>
                <span class="inline-block px-3 py-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/40 rounded-full mb-4">Security First</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-5 leading-tight">
                    Built with security at every layer
                </h2>
                <p class="text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                    Every interaction is logged, every access is verified, and every piece of evidence is protected by multiple layers of security controls.
                </p>

                <div class="space-y-4">
                    @foreach([
                        ['Password Policy', 'Minimum 12 characters with complexity requirements, expiry enforcement, and history tracking to prevent reuse.', 'indigo'],
                        ['Account Lockout', 'Automatic account lockout after 5 failed login attempts with configurable lockout duration.', 'red'],
                        ['Session Management', 'Track active sessions per device, revoke individual sessions, and enforce concurrent session limits.', 'emerald'],
                        ['Privilege Escalation Prevention', 'Rank-based access control prevents users from assigning permissions or ranks higher than their own.', 'amber'],
                    ] as [$title, $desc, $color])
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-lg bg-{{ $color }}-100 dark:bg-{{ $color }}-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-{{ $color }}-600 dark:text-{{ $color }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm mb-0.5">{{ $title }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     RANK SYSTEM SECTION
═══════════════════════════════════════════════ --}}
<section id="about" class="py-20 lg:py-28 bg-slate-50 dark:bg-slate-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <span class="inline-block px-3 py-1 text-xs font-semibold text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/40 rounded-full mb-3">Hierarchical Access</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-4">10-Level Rank System</h2>
            <p class="text-slate-500 dark:text-slate-400 max-w-xl mx-auto">
                Fine-grained access control through a hierarchical rank system. Higher ranks unlock more sensitive operations.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 max-w-4xl mx-auto">
            @foreach([
                [1,  'Rank 1',  'Observer',      'slate',  'Read-only access'],
                [2,  'Rank 2',  'Analyst',       'slate',  'Basic operations'],
                [3,  'Rank 3',  'Investigator',  'blue',   'Case management'],
                [4,  'Rank 4',  'Senior Inv.',   'blue',   'Team operations'],
                [5,  'Rank 5',  'Team Lead',     'cyan',   'Team oversight'],
                [6,  'Rank 6',  'Supervisor',    'teal',   'Dept. oversight'],
                [7,  'Rank 7',  'Manager',       'yellow', 'Dept. management'],
                [8,  'Rank 8',  'Admin',         'orange', 'User management'],
                [9,  'Rank 9',  'Sr. Admin',     'red',    'Role management'],
                [10, 'Rank 10', 'Super Admin',   'purple', 'Full system access'],
            ] as [$rank, $label, $title, $color, $desc])
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 text-center hover:shadow-md hover:border-{{ $color }}-300 dark:hover:border-{{ $color }}-700 transition-all duration-200 group">
                <div class="w-10 h-10 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/40 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <span class="text-sm font-bold text-{{ $color }}-700 dark:text-{{ $color }}-400">{{ $rank }}</span>
                </div>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
                <p class="text-xs text-slate-400 mt-0.5 leading-tight">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CTA SECTION
═══════════════════════════════════════════════ --}}
<section class="py-20 lg:py-28 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 relative overflow-hidden">

    {{-- Background pattern --}}
    <div class="absolute inset-0 opacity-10"
         style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 text-white text-xs font-semibold mb-6">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Secure Access Required
        </div>

        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5 leading-tight">
            Ready to access the system?
        </h2>
        <p class="text-indigo-200 text-lg mb-10 max-w-2xl mx-auto">
            Sign in with your authorized credentials to access the Secure Digital Evidence Management System. All access is monitored and logged.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-indigo-700 bg-white hover:bg-indigo-50 rounded-xl transition-all duration-150 shadow-xl hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-indigo-700 bg-white hover:bg-indigo-50 rounded-xl transition-all duration-150 shadow-xl hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Sign In to System
                </a>
                @if(Route::has('register'))
                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-white/20 hover:bg-white/30 border border-white/30 rounded-xl transition-all duration-150 hover:-translate-y-0.5">
                    Request Access
                </a>
                @endif
            @endauth
        </div>

        <p class="mt-8 text-indigo-300 text-sm">
            🔒 This system is for authorized personnel only. Unauthorized access is prohibited and will be prosecuted.
        </p>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════ --}}
<footer class="bg-slate-900 dark:bg-slate-950 border-t border-slate-800 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">SDEMS</p>
                    <p class="text-slate-500 text-xs">Secure Digital Evidence Management System</p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-xs text-slate-500">
                <span>© {{ date('Y') }} SDEMS. All rights reserved.</span>
                <span class="hidden sm:block">|</span>
                <span class="hidden sm:block">Authorized Personnel Only</span>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
