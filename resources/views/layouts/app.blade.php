<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode')==='true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SDEMS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 transition-colors duration-200">
    @include('layouts.navigation')
    @isset($header)
    <header class="bg-white dark:bg-slate-800 shadow-sm border-b border-slate-200 dark:border-slate-700">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">{{ $header }}</div>
    </header>
    @endisset
    <main>{{ $slot }}</main>
    @stack('scripts')
</body>
</html>
