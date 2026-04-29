@props(['title' => 'Admin'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="sdemsApp()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config("app.name") }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body class="font-sans bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 transition-colors duration-200">

{{-- ═══════════════════════════════════════════════════════════
     MAIN WRAPPER — sidebar + content
═══════════════════════════════════════════════════════════ --}}
<div class="flex h-screen overflow-hidden">

{{-- ══════════════════════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════════════════════ --}}
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 dark:bg-slate-950 border-r border-slate-700/50
           flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">

    {{-- Logo --}}
    <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-700/50 flex-shrink-0">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div>
            <p class="text-white font-bold text-sm tracking-wide leading-none">SDEMS</p>
            <p class="text-slate-500 text-xs mt-0.5">Evidence Management</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

        {{-- Section: Main --}}
        <p class="px-3 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">Main</p>

        <a href="{{ route("admin.dashboard") }}"
           class="nav-item {{ request()->routeIs("admin.dashboard") ? "nav-item-active" : "nav-item-inactive" }}">
            <svg class="w-4.5 h-4.5 w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- Section: Users --}}
        <p class="px-3 pt-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">User Management</p>

        <a href="{{ route("admin.users.index") }}"
           class="nav-item {{ request()->routeIs("admin.users.*") ? "nav-item-active" : "nav-item-inactive" }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Users</span>
            @php $userCount = \App\Models\User::count(); @endphp
            @if($userCount > 0)
            <span class="ml-auto text-xs bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full">{{ $userCount }}</span>
            @endif
        </a>

        @role("super-admin")
        <a href="{{ route("admin.roles.index") }}"
           class="nav-item {{ request()->routeIs("admin.roles.*") ? "nav-item-active" : "nav-item-inactive" }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <span>Roles & Permissions</span>
        </a>
        @endrole

        {{-- Section: Evidence --}}
        <p class="px-3 pt-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">Evidence</p>

        <a href="{{ route('custody.index') }}"
           class="nav-item {{ request()->routeIs('custody.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            <span>Chain of Custody</span>
            @php $cocCount = \App\Models\Evidence::count(); @endphp
            @if($cocCount > 0)
            <span class="ml-auto text-xs bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full">{{ $cocCount }}</span>
            @endif
        </a>

        @if(auth()->user()->hasMinimumRank(5))
        <a href="{{ route('integrity.index') }}"
           class="nav-item {{ request()->routeIs('integrity.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Integrity</span>
        </a>
        @endif

        <p class="px-3 pt-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">Monitoring</p>

        @if(auth()->user()->hasMinimumRank(5))
        <a href="{{ route('audit-logs.index') }}"
           class="nav-item {{ request()->routeIs('audit-logs.*') ? 'nav-item-active' : 'nav-item-inactive' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Audit Logs</span>
            @php $todayLogs = \Spatie\Activitylog\Models\Activity::whereDate('created_at', today())->count(); @endphp
            @if($todayLogs > 0)
            <span class="ml-auto text-xs bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded-full">{{ $todayLogs }}</span>
            @endif
        </a>
        @endif

        @role('admin|super-admin')
        <a href="{{ route("admin.activity-log") }}"
           class="nav-item {{ request()->routeIs("admin.activity-log") ? "nav-item-active" : "nav-item-inactive" }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span>Raw Activity Log</span>
        </a>
        @endrole

        {{-- Section: Account --}}
        <p class="px-3 pt-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">Account</p>

        <a href="{{ route("profile.edit") }}"
           class="nav-item {{ request()->routeIs("profile.*") ? "nav-item-active" : "nav-item-inactive" }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>My Profile</span>
        </a>

    </nav>

    {{-- User info at bottom --}}
    <div class="flex-shrink-0 p-4 border-t border-slate-700/50">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600
                        flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-200 truncate">{{ auth()->user()->name }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @php $rank = auth()->user()->rank; @endphp
                    <x-rank-badge :rank="$rank" size="xs"/>
                    <span class="text-xs text-slate-500">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route("logout") }}" class="mt-3">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-slate-400
                           hover:bg-red-500/10 hover:text-red-400 transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign Out
            </button>
        </form>
    </div>
</aside>

{{-- Mobile overlay --}}
<div x-show="sidebarOpen"
     @click="sidebarOpen = false"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-black/60 lg:hidden"
     x-cloak></div>

{{-- ══════════════════════════════════════════════════════════
     MAIN CONTENT AREA
══════════════════════════════════════════════════════════ --}}
<div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    {{-- Top Navbar --}}
    <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700
                   flex items-center px-4 gap-4 flex-shrink-0 z-30">

        {{-- Mobile menu toggle --}}
        <button @click="sidebarOpen = !sidebarOpen"
                class="btn-icon text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 lg:hidden"
                aria-label="Toggle sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Page title (mobile) --}}
        <h1 class="text-base font-semibold text-slate-800 dark:text-slate-100 lg:hidden truncate">
            {{ $title ?? "Admin" }}
        </h1>

        {{-- Search bar --}}
        <div class="hidden md:flex flex-1 max-w-md">
            <div class="relative w-full">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" placeholder="Search users, logs…"
                       class="w-full pl-9 pr-4 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0
                              rounded-lg text-slate-700 dark:text-slate-200 placeholder-slate-400
                              focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2">

            {{-- Dark mode toggle --}}
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="btn-icon text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"
                    :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>

            {{-- Notifications --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="btn-icon text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 relative"
                        aria-label="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 mt-2 w-80 card shadow-xl z-50 overflow-hidden" x-cloak>
                    <div class="card-header">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notifications</span>
                        <span class="badge badge-blue">3 new</span>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer">
                            <p class="text-sm text-slate-700 dark:text-slate-300">New user registered</p>
                            <p class="text-xs text-slate-400 mt-0.5">2 minutes ago</p>
                        </div>
                        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer">
                            <p class="text-sm text-slate-700 dark:text-slate-300">Failed login attempt detected</p>
                            <p class="text-xs text-slate-400 mt-0.5">15 minutes ago</p>
                        </div>
                        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer">
                            <p class="text-sm text-slate-700 dark:text-slate-300">Password expiry reminder</p>
                            <p class="text-xs text-slate-400 mt-0.5">1 hour ago</p>
                        </div>
                    </div>
                    <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
                        <a href="{{ route("admin.activity-log") }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            View all activity →
                        </a>
                    </div>
                </div>
            </div>

            {{-- User dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-lg
                               hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                        aria-label="User menu">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600
                                flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200 leading-none">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Rank {{ auth()->user()->rank }}</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 mt-2 w-56 card shadow-xl z-50 overflow-hidden py-1" x-cloak>

                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        <div class="mt-1.5">
                            <x-rank-badge :rank="auth()->user()->rank" size="xs"/>
                        </div>
                    </div>

                    <a href="{{ route("profile.edit") }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300
                              hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        My Profile
                    </a>

                    <a href="{{ route("admin.dashboard") }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300
                              hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </a>

                    <div class="border-t border-slate-100 dark:border-slate-700 mt-1 pt-1">
                        <form method="POST" action="{{ route("logout") }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 dark:text-red-400
                                           hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 overflow-y-auto p-6">

        {{-- Flash messages --}}
        @if(session("success"))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="alert-success mb-5 animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>{{ session("success") }}</p>
            <button @click="show = false" class="ml-auto text-emerald-600 hover:text-emerald-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        @if($errors->any())
        <div x-data="{ show: true }" x-show="show"
             class="alert-error mb-5 animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <ul class="flex-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button @click="show = false" class="ml-auto text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        {{ $slot }}
    </main>
</div>
</div>

{{-- Alpine.js global app data --}}
<script>
function sdemsApp() {
    return {
        sidebarOpen: false,
        darkMode: localStorage.getItem("darkMode") === "true",
        init() {
            // Close sidebar on large screens
            this.$watch("sidebarOpen", val => {
                if (window.innerWidth >= 1024) this.sidebarOpen = false;
            });
        }
    }
}
</script>

</body>
</html>

