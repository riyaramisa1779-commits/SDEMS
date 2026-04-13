<x-admin-layout title="Activity Logs">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Activity Logs</h1>
        <p class="text-sm text-slate-500 mt-0.5">Complete audit trail of all system events</p>
    </div>
    <a href="{{ route('admin.activity-log') }}?export=csv" class="btn-secondary btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export CSV
    </a>
</div>
<div class="card mb-5"><div class="card-body">
<form method="GET" class="flex flex-wrap gap-3 items-end">
    <div><label class="form-label">From</label><input type="date" name="from" value="{{ request('from') }}" class="form-input"/></div>
    <div><label class="form-label">To</label><input type="date" name="to" value="{{ request('to') }}" class="form-input"/></div>
    <div class="w-44">
        <label class="form-label">User</label>
        <select name="user_id" class="form-input">
            <option value="">All Users</option>
            @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id')==$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-40">
        <label class="form-label">Action Type</label>
        <select name="log_name" class="form-input">
            <option value="">All Types</option>
            @foreach($logNames as $name)
            <option value="{{ $name }}" {{ request('log_name')===$name ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2 pb-0.5">
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.activity-log') }}" class="btn-secondary btn-sm">Reset</a>
    </div>
</form>
</div></div>
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead><tr>
                <th>Timestamp</th><th>User</th><th>Type</th><th>Description</th><th>Properties</th>
            </tr></thead>
            <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="whitespace-nowrap text-xs text-slate-400">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                            {{ strtoupper(substr($log->causer?->name ?? 'S', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $log->causer?->name ?? 'System' }}</p>
                            @if($log->causer)
                            <x-rank-badge :rank="$log->causer->rank" size="xs"/>
                            @endif
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-blue">{{ $log->log_name }}</span></td>
                <td class="text-sm text-slate-600 dark:text-slate-400 max-w-xs">{{ $log->description }}</td>
                <td>
                    @if($log->properties->isNotEmpty())
                    <details class="text-xs text-slate-500 cursor-pointer">
                        <summary class="hover:text-indigo-600 dark:hover:text-indigo-400 select-none">View details</summary>
                        <pre class="mt-2 bg-slate-50 dark:bg-slate-900 rounded-lg p-3 text-xs overflow-x-auto max-w-xs">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                    </details>
                    @else <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-16 text-center text-slate-400 text-sm">No activity logs found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">{{ $logs->links() }}</div>
    @endif
</div>
</x-admin-layout>


