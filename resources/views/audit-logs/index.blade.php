
<x-admin-layout title="Audit Logs">
<div x-data="auditLogs()">

{{-- ── Page Header ──────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Audit Logs
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">Forensic audit trail — all system events logged for legal review</p>
    </div>
    @if(auth()->user()->hasMinimumRank(5))
    <a href="{{ route('audit-logs.index') }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'csv'])) }}"
       class="btn-secondary btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export CSV
    </a>
    @endif
</div>

{{-- ── Summary Stats ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon bg-indigo-100 dark:bg-indigo-900/40">
            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['total_logs']) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Total Log Entries</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/40">
            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['logs_today']) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Events Today</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-blue-100 dark:bg-blue-900/40">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">
                {{ $stats['most_active_user']?->name ?? 'N/A' }}
            </p>
            <p class="text-xs text-slate-500 mt-0.5">Most Active Today</p>
            @if($stats['most_active_user'])
            <x-rank-badge :rank="$stats['most_active_user']->rank" size="xs"/>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-red-100 dark:bg-red-900/40">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['critical_today']) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Critical Events Today</p>
        </div>
    </div>
</div>

{{-- ── Filter Bar ───────────────────────────────────────────────────────── --}}
<div class="card mb-5">
    <div class="card-header">
        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filter Logs
        </span>
        <button @click="filtersOpen = !filtersOpen"
                class="btn-icon text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-4 h-4 transition-transform duration-200" :class="filtersOpen ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>
    <div x-show="filtersOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="card-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Date From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label">Date To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-input">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} (Rank {{ $u->rank }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Action Type</label>
                    <select name="log_name" class="form-input">
                        <option value="">All Types</option>
                        @foreach($logNames as $name)
                        <option value="{{ $name }}" {{ request('log_name') === $name ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Subject Type</label>
                    <select name="subject_type" class="form-input">
                        <option value="">All Subjects</option>
                        <option value="Evidence"       {{ request('subject_type') === 'Evidence'       ? 'selected' : '' }}>Evidence</option>
                        <option value="User"           {{ request('subject_type') === 'User'           ? 'selected' : '' }}>User</option>
                        <option value="ChainOfCustody" {{ request('subject_type') === 'ChainOfCustody' ? 'selected' : '' }}>Chain of Custody</option>
                        <option value="EvidenceHash"   {{ request('subject_type') === 'EvidenceHash'   ? 'selected' : '' }}>Evidence Hash</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip" value="{{ request('ip') }}"
                           placeholder="e.g. 192.168.1.1" class="form-input"/>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Search Description</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search descriptions, log types…"
                               class="form-input pl-9"/>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-1">
                <button type="submit" class="btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Apply Filters
                </button>
                <a href="{{ route('audit-logs.index') }}" class="btn-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
                @if(request()->hasAny(['from','to','user_id','log_name','subject_type','ip','search']))
                <span class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filters active
                </span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Log Table ─────────────────────────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <div class="card-header">
        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
            Log Entries
            <span class="ml-2 text-xs font-normal text-slate-400">({{ $logs->total() }} total)</span>
        </span>
        <span class="text-xs text-slate-400">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action Type</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th class="text-right">Details</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
            @php
                $isCritical = in_array($log->log_name, ['integrity_alert', 'rank_access']);
                $isWarning  = $log->log_name === 'auth' && str_contains(strtolower($log->description ?? ''), 'fail');
                $subjectLabel = $log->subject_type ? class_basename($log->subject_type) : null;
                $ip = $log->properties['ip'] ?? null;
            @endphp
            <tr class="{{ $isCritical ? 'bg-red-50/60 dark:bg-red-900/10' : ($isWarning ? 'bg-amber-50/60 dark:bg-amber-900/10' : '') }}">

                {{-- Timestamp --}}
                <td class="whitespace-nowrap">
                    <div class="text-xs font-mono text-slate-600 dark:text-slate-400">
                        {{ $log->created_at->format('d M Y') }}
                    </div>
                    <div class="text-xs text-slate-400 font-mono">
                        {{ $log->created_at->format('H:i:s') }}
                    </div>
                </td>

                {{-- User --}}
                <td>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold
                            {{ $log->causer ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }}">
                            {{ strtoupper(substr($log->causer?->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate max-w-[120px]">
                                {{ $log->causer?->name ?? 'System' }}
                            </p>
                            @if($log->causer)
                            <x-rank-badge :rank="$log->causer->rank" size="xs"/>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Action Type --}}
                <td>
                    @php
                        $badgeClass = match(true) {
                            str_contains($log->log_name ?? '', 'integrity_alert') => 'badge-red',
                            str_contains($log->log_name ?? '', 'integrity')       => 'badge-green',
                            str_contains($log->log_name ?? '', 'evidence')        => 'badge-blue',
                            str_contains($log->log_name ?? '', 'custody')         => 'badge-purple',
                            str_contains($log->log_name ?? '', 'auth')            => 'badge-yellow',
                            str_contains($log->log_name ?? '', 'user')            => 'badge-orange',
                            str_contains($log->log_name ?? '', 'audit')           => 'badge-gray',
                            default                                                => 'badge-gray',
                        };
                    @endphp
                    <span class="{{ $badgeClass }}">{{ $log->log_name }}</span>
                    @if($isCritical)
                    <div class="mt-1"><span class="badge badge-red text-xs">⚠ Critical</span></div>
                    @endif
                </td>

                {{-- Subject --}}
                <td>
                    @if($subjectLabel)
                    <div class="text-xs">
                        <span class="font-medium text-slate-600 dark:text-slate-400">{{ $subjectLabel }}</span>
                        @if($log->subject_id)
                        <span class="text-slate-400 font-mono ml-1">
                            #{{ is_string($log->subject_id) && strlen($log->subject_id) > 8
                                ? substr($log->subject_id, 0, 8) . '…'
                                : $log->subject_id }}
                        </span>
                        @endif
                        @if($log->properties['case_number'] ?? null)
                        <div class="text-slate-400 mt-0.5">Case: {{ $log->properties['case_number'] }}</div>
                        @endif
                    </div>
                    @else
                    <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>

                {{-- Description --}}
                <td class="max-w-xs">
                    <p class="text-sm text-slate-600 dark:text-slate-400 truncate" title="{{ $log->description }}">
                        {{ $log->description }}
                    </p>
                </td>

                {{-- IP --}}
                <td>
                    @if($ip)
                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ $ip }}</span>
                    @else
                    <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                    @endif
                </td>

                {{-- Details --}}
                <td class="text-right">
                    <a href="{{ route('audit-logs.show', $log->id) }}"
                       class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                        View
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-16 text-center">
                    <div class="flex flex-col items-center gap-3 text-slate-400">
                        <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm">No audit log entries found for the selected filters.</p>
                        <a href="{{ route('audit-logs.index') }}" class="text-xs text-indigo-500 hover:underline">Clear filters</a>
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-slate-400">
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries
        </p>
        {{ $logs->links() }}
    </div>
    @endif
</div>

{{-- ── Legal Notice ─────────────────────────────────────────────────────── --}}
<div class="mt-5 alert alert-info text-xs">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p>
        <strong>Legal Notice:</strong> This audit log is a tamper-evident record of all system activity.
        Accessing, exporting, or modifying this log is itself recorded. All entries are admissible as
        digital evidence in legal proceedings. Unauthorised access is a criminal offence.
    </p>
</div>

</div>

<script>
function auditLogs() {
    return {
        filtersOpen: true,
    }
}
</script>
</x-admin-layout>
