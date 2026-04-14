<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-slate-800 dark:text-slate-200">
            Welcome back, {{ Auth::user()->name }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Quick actions --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- Upload Evidence --}}
            <a href="{{ route('evidence.create') }}"
               class="group flex items-start gap-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/60 transition-colors">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Upload Evidence</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Add a new file to the evidence vault</p>
                </div>
            </a>

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}"
               class="group flex items-start gap-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/60 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">My Profile</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage account settings & 2FA</p>
                </div>
            </a>

            {{-- Admin Panel (admin/super-admin only) --}}
            @role('admin|super-admin')
            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-start gap-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5 hover:border-purple-400 dark:hover:border-purple-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-900/60 transition-colors">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Admin Panel</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Users, roles & activity logs</p>
                </div>
            </a>
            @endrole

        </div>

        {{-- User info strip --}}
        <div class="mt-6 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm px-5 py-4 flex flex-wrap items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
            <span>Rank <span class="font-semibold text-slate-800 dark:text-slate-200">{{ Auth::user()->rank }}</span></span>
            <span class="text-slate-300 dark:text-slate-600">|</span>
            <span>{{ Auth::user()->email }}</span>
            <span class="text-slate-300 dark:text-slate-600">|</span>
            <span class="inline-flex items-center gap-1">
                <span class="w-2 h-2 rounded-full {{ Auth::user()->is_active ? 'bg-emerald-500' : 'bg-red-400' }}"></span>
                {{ Auth::user()->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

    </div>
</x-app-layout>
