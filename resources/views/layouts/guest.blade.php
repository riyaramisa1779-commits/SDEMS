<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config("app.name", "SDEMS") }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body class="font-sans antialiased h-full bg-slate-950">

<div class="min-h-screen flex">

    {{-- Left branding panel (hidden on mobile) --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex-col justify-between p-12">

        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 32px 32px;"></div>

        {{-- Logo --}}
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-lg leading-none">SDEMS</p>
                <p class="text-indigo-300 text-xs">Secure Digital Evidence Management</p>
            </div>
        </div>

        {{-- Center content --}}
        <div class="relative">
            <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                Secure. Reliable.<br>
                <span class="text-indigo-400">Trusted.</span>
            </h1>
            <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                A comprehensive digital evidence management platform built for law enforcement and legal professionals.
            </p>

            {{-- Feature list --}}
            <div class="mt-8 space-y-3">
                @foreach(["End-to-end encrypted evidence storage", "Role-based access control with rank hierarchy", "Complete audit trail and activity logging", "Two-factor authentication support"] as $feature)
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-slate-300 text-sm">{{ $feature }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <div class="relative">
            <p class="text-slate-600 text-xs">© {{ date("Y") }} SDEMS. All rights reserved. Authorized personnel only.</p>
        </div>
    </div>

    {{-- Right auth panel --}}
    <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-12 bg-white dark:bg-slate-900">

        {{-- Mobile logo --}}
        <div class="lg:hidden flex items-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <span class="text-slate-800 font-bold text-xl">SDEMS</span>
        </div>

        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>
</div>

</body>
</html>
