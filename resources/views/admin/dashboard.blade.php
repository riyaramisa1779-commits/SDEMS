<x-admin-layout title="Dashboard">

{{-- Welcome banner --}}
<div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl p-6 mb-6 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10"
         style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
    <div class="relative flex items-center justify-between">
        <div>
            <p class="text-indigo-200 text-sm font-medium">Welcome back,</p>
            <h1 class="text-2xl font-bold text-white mt-0.5">{{ auth()->user()->name }}</h1>
            <div class="flex items-center gap-3 mt-2">
                <x-rank-badge :rank="auth()->user()->rank"/>
                <span class="text-indigo-200 text-sm">{{ auth()->user()->getRoleNames()->first() }}</span>
                <span class="text-indigo-300 text-xs">Last login: {{ now()->format('d M Y, H:i') }}</span>
            </div>
        </div>
        <div class="hidden sm:block">
            <div class="w-16 h-16 rounded-full bg-white/10 border-2 border-white/20 flex items-center justify-center">
                <span class="text-2xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Stats grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    {{-- Total Users --}}
    <div class="stat-card">
        <div class="stat-icon bg-indigo-100 dark:bg-indigo-900/40">
            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Users</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $stats['total_users'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">All registered accounts</p>
        </div>
    </div>

    {{-- Active Users --}}
    <div class="stat-card">
        <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/40">
            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Active Users</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $stats['active_users'] }}</p>
            <p class="text-xs text-emerald-500 mt-0.5">↑ Currently active</p>
        </div>
    </div>

    {{-- Inactive --}}
    <div class="stat-card">
        <div class="stat-icon bg-amber-100 dark:bg-amber-900/40">
            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Inactive</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $stats['inactive_users'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Deactivated accounts</p>
        </div>
    </div>

    {{-- Locked --}}
    <div class="stat-card">
        <div class="stat-icon bg-red-100 dark:bg-red-900/40">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Locked</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $stats['locked_users'] }}</p>
            <p class="text-xs text-red-400 mt-0.5">Requires attention</p>
        </div>
    </div>
</div>

{{-- Bottom grid: Activity + Quick links --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Recent Activity --}}
    <div class="xl:col-span-2 card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Recent Activity</h2>
                <p class="text-xs text-slate-400 mt-0.5">Last 10 system events</p>
            </div>
            <a href="{{ route('admin.activity-log') }}" class="btn-ghost btn-sm">
                View all →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['recent_logs'] as $log)
                    <tr>
                        <td class="whitespace-nowrap text-xs text-slate-400">
                            {{ $log->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ strtoupper(substr($log->causer?->name ?? 'S', 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ $log->causer?->name ?? 'System' }}
                                </span>
                            </div>
                        </td>
                        <td class="text-sm text-slate-600 dark:text-slate-400 max-w-xs truncate">
                            {{ $log->description }}
                        </td>
                        <td>
                            <span class="badge badge-blue">{{ $log->log_name }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-slate-400 text-sm">No activity yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="space-y-4">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.users.create') }}"
                   class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20
                          hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Create User</p>
                        <p class="text-xs text-slate-400">Add a new system user</p>
                    </div>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700/40
                          hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-slate-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Manage Users</p>
                        <p class="text-xs text-slate-400">View all user accounts</p>
                    </div>
                </a>

                <a href="{{ route('admin.activity-log') }}"
                   class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700/40
                          hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-slate-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Activity Logs</p>
                        <p class="text-xs text-slate-400">Audit trail & events</p>
                    </div>
                </a>

                @role('super-admin')
                <a href="{{ route('admin.roles.index') }}"
                   class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-700/40
                          hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-purple-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Roles & Permissions</p>
                        <p class="text-xs text-slate-400">Manage access control</p>
                    </div>
                </a>
                @endrole
            </div>
        </div>

        {{-- System status --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3">System Status</h3>
            <div class="space-y-2.5">
                @foreach([
                    ['Authentication', 'Operational', 'green'],
                    ['Database', 'Operational', 'green'],
                    ['Activity Logging', 'Operational', 'green'],
                    ['Email Service', 'Configured', 'blue'],
                ] as [$service, $status, $color])
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-600 dark:text-slate-400">{{ $service }}</span>
                    <span class="badge badge-{{ $color }} text-xs">{{ $status }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

</x-admin-layout>

