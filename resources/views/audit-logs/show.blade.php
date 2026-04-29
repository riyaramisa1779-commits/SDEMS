
<x-admin-layout title="Audit Log Entry">

{{-- ── Breadcrumb ──────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
    <a href="{{ route('audit-logs.index') }}"
       class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
        Audit Logs
    </a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-700 dark:text-slate-300 font-medium">Entry #{{ $log->id }}</span>
</div>

@php
    $isCritical   = in_array($log->log_name, ['integrity_alert', 'rank_access']);
    $isWarning    = $log->log_name === 'auth' && str_contains(strtolower($log->description ?? ''), 'fail');
    $subjectLabel = $log->subject_type ? class_basename($log->subject_type) : null;
    $ip           = $log->properties['ip'] ?? null;
    $props        = $log->properties->except(['password', 'token', 'secret', 'two_factor_secret']);
@endphp

{{-- ── Critical / Warning Banner ──────────────────────────────────────── --}}
@if($isCritical)
<div class="alert alert-error mb-5">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <p class="font-semibold">Critical Security Event</p>
        <p class="text-sm mt-0.5">This log entry represents a high-severity security event that may require immediate review.</p>
    </div>
</div>
@elseif($isWarning)
<div class="alert alert-warning mb-5">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <p>This log entry indicates a failed or suspicious action that may warrant review.</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Main Details ──────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Core Info --}}
        <div class="card">
            <div class="card-header">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Log Entry Details</span>
                @php
                    $badgeClass = match(true) {
                        str_contains($log->log_name ?? '', 'integrity_alert') => 'badge-red',
                        str_contains($log->log_name ?? '', 'integrity')       => 'badge-green',
                        str_contains($log->log_name ?? '', 'evidence')        => 'badge-blue',
                        str_contains($log->log_name ?? '', 'custody')         => 'badge-purple',
                        str_contains($log->log_name ?? '', 'auth')            => 'badge-yellow',
                        str_contains($log->log_name ?? '', 'user')            => 'badge-orange',
                        default                                                => 'badge-gray',
                    };
                @endphp
                <span class="{{ $badgeClass }}">{{ $log->log_name }}</span>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Log ID</p>
                        <p class="text-sm font-mono text-slate-700 dark:text-slate-300">#{{ $log->id }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Timestamp</p>
                        <p class="text-sm font-mono text-slate-700 dark:text-slate-300">
                            {{ $log->created_at->format('d M Y, H:i:s') }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Log Type</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 font-mono">{{ $log->log_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Event</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $log->event ?? '—' }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50
                               rounded-lg px-4 py-3 border border-slate-100 dark:border-slate-700">
                        {{ $log->description }}
                    </p>
                </div>

                @if($subjectLabel)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Subject Type</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $subjectLabel }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Subject ID</p>
                        <p class="text-sm font-mono text-slate-700 dark:text-slate-300 break-all">
                            {{ $log->subject_id ?? '—' }}
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Properties / Context --}}
        @if($props->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Event Properties</span>
                <span class="text-xs text-slate-400">{{ $props->count() }} field(s)</span>
            </div>
            <div class="card-body">
                <div class="space-y-0">
                    @foreach($props as $key => $value)
                    @if(!in_array($key, ['old', 'attributes']))
                    <div class="flex items-start gap-3 py-2.5 border-b border-slate-100 dark:border-slate-700/60 last:border-0">
                        <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400 w-36 flex-shrink-0 pt-0.5">
                            {{ $key }}
                        </span>
                        <span class="text-sm text-slate-700 dark:text-slate-300 break-all flex-1">
                            @if(is_array($value))
                                <pre class="text-xs bg-slate-50 dark:bg-slate-900 rounded p-2 overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                    @endif
                    @endforeach
                </div>

                {{-- Before / After diff --}}
                @if($props->has('old') || $props->has('attributes'))
                <div class="mt-5 pt-5 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Field Changes (Before / After)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($props->has('old'))
                        <div>
                            <p class="text-xs font-semibold text-red-500 mb-2 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                                Before
                            </p>
                            <pre class="text-xs bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800
                                        rounded-lg p-3 overflow-x-auto text-red-700 dark:text-red-300">{{ json_encode($props['old'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                        @if($props->has('attributes'))
                        <div>
                            <p class="text-xs font-semibold text-emerald-500 mb-2 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                After
                            </p>
                            <pre class="text-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800
                                        rounded-lg p-3 overflow-x-auto text-emerald-700 dark:text-emerald-300">{{ json_encode($props['attributes'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Raw JSON --}}
                <details class="mt-4">
                    <summary class="text-xs text-slate-400 hover:text-indigo-500 cursor-pointer select-none">
                        View raw JSON
                    </summary>
                    <pre class="mt-2 text-xs bg-slate-900 text-slate-300 rounded-lg p-4 overflow-x-auto">{{ json_encode($props, JSON_PRETTY_PRINT) }}</pre>
                </details>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Sidebar ────────────────────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Causer --}}
        <div class="card">
            <div class="card-header">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Performed By</span>
            </div>
            <div class="card-body">
                @if($log->causer)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600
                                flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        {{ strtoupper(substr($log->causer->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $log->causer->name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $log->causer->email }}</p>
                        <div class="mt-1.5">
                            <x-rank-badge :rank="$log->causer->rank" size="sm"/>
                        </div>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-xs border-t border-slate-100 dark:border-slate-700 pt-4">
                    <div class="flex justify-between">
                        <span class="text-slate-400">User ID</span>
                        <span class="font-mono text-slate-600 dark:text-slate-400">{{ $log->causer->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Account Status</span>
                        <span class="{{ $log->causer->is_active ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $log->causer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    @if($ip)
                    <div class="flex justify-between">
                        <span class="text-slate-400">IP Address</span>
                        <span class="font-mono text-slate-600 dark:text-slate-400">{{ $ip }}</span>
                    </div>
                    @endif
                </div>
                @else
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-slate-200 dark:bg-slate-700
                                flex items-center justify-center text-slate-400 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-600 dark:text-slate-400">System</p>
                        <p class="text-xs text-slate-400">Automated process</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Navigation --}}
        <div class="card">
            <div class="card-body space-y-2">
                <a href="{{ route('audit-logs.index') }}" class="btn-secondary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Audit Logs
                </a>
                @if($log->subject_type === \App\Models\Evidence::class && $log->subject_id)
                <a href="{{ route('custody.show', $log->subject_id) }}"
                   class="btn-ghost w-full justify-center text-sm">
                    View Evidence Chain
                </a>
                @endif
            </div>
        </div>

        {{-- Integrity stamp --}}
        <div class="card">
            <div class="card-body text-center py-6">
                <svg class="w-8 h-8 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Tamper-Evident Record</p>
                <p class="text-xs text-slate-400 mt-1">This entry is immutable and court-admissible.</p>
            </div>
        </div>
    </div>
</div>

</x-admin-layout>
