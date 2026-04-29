<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Integrity Verification</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">SHA-256 tamper detection for all evidence files</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-rank-badge :rank="auth()->user()->rank" />
                @if(auth()->user()->hasMinimumRank(8))
                <div x-data="bulkVerify()">
                    <button @click="run()" :disabled="running" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                        <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <svg x-show="running" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="running ? 'Running...' : 'Bulk Verify All'"></span>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if(session('tamper_alert'))
        <div class="flex items-start gap-3 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 px-5 py-4">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-semibold text-red-800 dark:text-red-300">Tampering Detected</p>
                <p class="text-sm text-red-700 dark:text-red-400 mt-0.5">{{ session('tamper_alert') }}</p>
            </div>
        </div>
        @endif

        {{-- Summary stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <div class="stat-icon bg-slate-100 dark:bg-slate-700"><svg class="w-6 h-6 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Evidence</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $stats['total'] }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/40"><svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Hash Present</p>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">{{ $stats['total'] - $stats['pending'] }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-amber-100 dark:bg-amber-900/40"><svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pending Hash</p>
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-400 mt-0.5">{{ $stats['pending'] }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-blue-100 dark:bg-blue-900/40"><svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Verify on Demand</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 font-medium">Click Verify Now per row</p>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasMinimumRank(8))
        <div x-data="bulkVerify()" x-show="done" x-cloak class="rounded-xl border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 p-5">
            <h3 class="text-sm font-semibold text-indigo-800 dark:text-indigo-300 mb-3">Bulk Verification Results</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-indigo-100 dark:border-indigo-800"><p class="text-2xl font-bold text-slate-800 dark:text-slate-100" x-text="results.total">-</p><p class="text-xs text-slate-500 mt-0.5">Total</p></div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-emerald-200 dark:border-emerald-800"><p class="text-2xl font-bold text-emerald-600" x-text="results.verified">-</p><p class="text-xs text-slate-500 mt-0.5">Verified</p></div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-red-200 dark:border-red-800"><p class="text-2xl font-bold text-red-600" x-text="results.tampered">-</p><p class="text-xs text-slate-500 mt-0.5">Tampered</p></div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-amber-200 dark:border-amber-800"><p class="text-2xl font-bold text-amber-600" x-text="results.pending">-</p><p class="text-xs text-slate-500 mt-0.5">Pending</p></div>
            </div>
            <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-3">Run at: <span x-text="results.run_at"></span></p>
        </div>
        @endif

        {{-- Filter bar --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="form-label">Case Number</label>
                    <input type="text" name="case_number" value="{{ request('case_number') }}" placeholder="e.g. CASE-2026-001" class="form-input w-52">
                </div>
                <div>
                    <label class="form-label">Hash Status</label>
                    <select name="status_filter" class="form-input w-44">
                        <option value="">All</option>
                        <option value="has_hash" {{ request('status_filter') === 'has_hash' ? 'selected' : '' }}>Has Hash</option>
                        <option value="pending" {{ request('status_filter') === 'pending' ? 'selected' : '' }}>Pending Hash</option>
                    </select>
                </div>
                <div class="flex gap-2 pb-0.5">
                    <button type="submit" class="btn-primary btn-sm">Filter</button>
                    <a href="{{ route('integrity.index') }}" class="btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        {{-- Evidence table --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Evidence Integrity Status</h3>
                <span class="text-xs text-slate-400">{{ $evidenceList->total() }} records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>File Name</th>
                            <th>Category</th>
                            <th>Uploaded By</th>
                            <th>Hash Stored</th>
                            <th>Integrity Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($evidenceList as $evidence)
                    @php
                        $hashState  = $evidence->latestHash ? 'has_hash' : 'no_hash';
                        $verifyUrl  = route('integrity.verify', $evidence);
                        $detailUrl  = route('integrity.show',   $evidence);
                    @endphp
                    <tr x-data="verifyRow('{{ $evidence->id }}', '{{ $verifyUrl }}', '{{ $hashState }}')">
                        <td>
                            <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $evidence->case_number }}</span>
                        </td>
                        <td>
                            <div class="max-w-xs">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{{ $evidence->title }}</p>
                                <p class="text-xs text-slate-400 font-mono truncate">{{ $evidence->original_name ?? '—' }}</p>
                            </div>
                        </td>
                        <td><span class="badge badge-blue">{{ ucfirst(str_replace('_', ' ', $evidence->category)) }}</span></td>
                        <td class="text-sm text-slate-600 dark:text-slate-400">{{ $evidence->uploader?->name ?? '—' }}</td>
                        <td>
                            @if($evidence->latestHash)
                                <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ substr($evidence->latestHash->hash_value, 0, 12) }}...</p>
                                <p class="text-xs text-slate-400">{{ $evidence->latestHash->generated_at->diffForHumans() }}</p>
                            @else
                                <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">No hash yet</span>
                            @endif
                        </td>
                        <td>
                            <template x-if="status === 'idle' && hasHash">
                                <span class="badge badge-gray">Not Checked</span>
                            </template>
                            <template x-if="status === 'idle' && !hasHash">
                                <span class="badge badge-yellow">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Pending
                                </span>
                            </template>
                            <template x-if="status === 'loading'">
                                <span class="badge badge-blue">
                                    <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Checking...
                                </span>
                            </template>
                            <template x-if="status === 'Verified'">
                                <span class="badge badge-green">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Verified
                                </span>
                            </template>
                            <template x-if="status === 'Tampered'">
                                <span class="badge badge-red">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    TAMPERED
                                </span>
                            </template>
                            <template x-if="status === 'Missing'">
                                <span class="badge badge-orange">Missing</span>
                            </template>
                            <template x-if="status === 'Pending'">
                                <span class="badge badge-yellow">Pending</span>
                            </template>
                            <p x-show="verifiedAt" x-cloak class="text-xs text-slate-400 mt-1" x-text="verifiedAt"></p>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <button @click="verify()" :disabled="loading"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                    <svg class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span x-text="loading ? 'Checking...' : 'Verify Now'"></span>
                                </button>
                                <a href="{{ $detailUrl }}"
                                   class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                    Details
                                </a>
                            </div>
                            <p x-show="status === 'Tampered'" x-cloak
                               class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Alert logged
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400 text-sm">No evidence records found.</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($evidenceList->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">{{ $evidenceList->links() }}</div>
            @endif
        </div>

        <div class="flex items-start gap-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 px-4 py-3">
            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                All verification attempts are logged to the immutable audit trail with user rank, timestamp, and IP address.
                SHA-256 hashes are computed by streaming the file through the private storage disk — raw file paths are never exposed.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
    function verifyRow(evidenceId, verifyUrl, hashState) {
        return {
            loading: false,
            status: 'idle',
            hasHash: hashState === 'has_hash',
            verifiedAt: null,
            async verify() {
                this.loading = true;
                this.status = 'loading';
                try {
                    const res = await fetch(verifyUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    this.status = data.status;
                    this.verifiedAt = data.verified_at;
                    if (data.status === 'Tampered') {
                        console.warn('INTEGRITY ALERT:', evidenceId, data.message);
                    }
                } catch(e) {
                    this.status = 'idle';
                    alert('Verification failed. Please try again.');
                } finally {
                    this.loading = false;
                }
            }
        }
    }

    function bulkVerify() {
        return {
            running: false,
            done: false,
            results: {},
            async run() {
                if (!confirm('Run a full integrity check across ALL evidence files? This may take a moment.')) return;
                this.running = true;
                this.done = false;
                try {
                    const res = await fetch('{{ route("integrity.bulk-verify") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        }
                    });
                    this.results = await res.json();
                    this.done = true;
                } catch(e) {
                    alert('Bulk verification failed. Please try again.');
                } finally {
                    this.running = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>