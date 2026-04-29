<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('integrity.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200 truncate max-w-md">Integrity Report: {{ $evidence->title }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $evidence->case_number }}</p>
                </div>
            </div>
            <x-rank-badge :rank="auth()->user()->rank" />
        </div>
    </x-slot>

    @php
        $verifyUrl = route('integrity.verify', $evidence);
        $rehashUrl = route('integrity.rehash', $evidence);
        $canRehash = auth()->user()->hasMinimumRank(8) ? 'true' : 'false';
    @endphp

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="integrityReport('{{ $evidence->id }}', '{{ $verifyUrl }}', '{{ $rehashUrl }}', {{ $canRehash }})">

        {{-- Big status result card --}}
        <div class="rounded-xl border-2 p-6 transition-colors duration-300"
             :class="{
                 'border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/20': result === 'Verified',
                 'border-red-400 bg-red-50 dark:border-red-700 dark:bg-red-900/20': result === 'Tampered',
                 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20': result === 'Pending' || result === 'idle',
                 'border-slate-300 bg-slate-50 dark:border-slate-600 dark:bg-slate-800/50': result === 'Missing'
             }">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0"
                     :class="{
                         'bg-emerald-100 dark:bg-emerald-900/40': result === 'Verified',
                         'bg-red-100 dark:bg-red-900/40': result === 'Tampered',
                         'bg-amber-100 dark:bg-amber-900/40': result === 'Pending' || result === 'idle',
                         'bg-slate-100 dark:bg-slate-700': result === 'Missing' || result === 'loading'
                     }">
                    <svg x-show="result === 'Verified'" class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <svg x-show="result === 'Tampered'" class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="result === 'idle' || result === 'Pending'" class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="result === 'loading'" class="w-8 h-8 text-blue-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-2xl font-bold"
                       :class="{
                           'text-emerald-700 dark:text-emerald-400': result === 'Verified',
                           'text-red-700 dark:text-red-400': result === 'Tampered',
                           'text-amber-700 dark:text-amber-400': result === 'Pending' || result === 'idle',
                           'text-slate-600 dark:text-slate-300': result === 'Missing' || result === 'loading'
                       }"
                       x-text="result === 'idle' ? 'Not Yet Verified' : result === 'loading' ? 'Verifying...' : result"></p>
                    <p class="text-sm mt-1 text-slate-600 dark:text-slate-400" x-text="message"></p>
                    <p x-show="verifiedAt" x-cloak class="text-xs text-slate-400 mt-1">Checked at: <span x-text="verifiedAt"></span></p>
                </div>
                <div class="flex flex-col gap-2 w-full sm:w-auto">
                    <button @click="verify()" :disabled="loading"
                            class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span x-text="loading ? 'Verifying...' : 'Verify Now'"></span>
                    </button>
                    @if(auth()->user()->hasMinimumRank(8))
                    <button @click="rehash()" :disabled="rehashing"
                            class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-4 h-4" :class="rehashing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="rehashing ? 'Re-hashing...' : 'Re-generate Hash'"></span>
                    </button>
                    @endif
                </div>
            </div>
            <div x-show="result === 'Tampered'" x-cloak class="mt-4 flex items-start gap-3 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 px-4 py-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <p class="font-semibold text-red-800 dark:text-red-300 text-sm">INTEGRITY VIOLATION DETECTED</p>
                    <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">The current file hash does not match the stored baseline. This evidence may have been tampered with. This event has been logged to the audit trail.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Hash comparison --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Hash Comparison</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Stored SHA-256 (Baseline)</p>
                        @if($evidence->latestHash)
                        <p class="font-mono text-xs text-slate-700 dark:text-slate-300 break-all bg-slate-50 dark:bg-slate-900 rounded-lg p-3 border border-slate-200 dark:border-slate-700">{{ $evidence->latestHash->hash_value }}</p>
                        <p class="text-xs text-slate-400 mt-1">Stored {{ $evidence->latestHash->generated_at->format('d M Y, H:i') }}</p>
                        @else
                        <p class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 border border-amber-200 dark:border-amber-700">No hash record found. The hash job may still be pending.</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Computed SHA-256 (Current File)</p>
                        <p x-show="!computedHash && result === 'idle'" class="text-xs text-slate-400 italic">Click Verify Now to compute the current hash.</p>
                        <p x-show="computedHash" x-cloak
                           class="font-mono text-xs break-all rounded-lg p-3 border"
                           :class="result === 'Verified' ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700' : 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700'"
                           x-text="computedHash"></p>
                    </div>
                    <div x-show="computedHash" x-cloak class="flex items-center gap-2 pt-1">
                        <div class="w-3 h-3 rounded-full" :class="result === 'Verified' ? 'bg-emerald-500' : 'bg-red-500'"></div>
                        <p class="text-xs font-semibold"
                           :class="result === 'Verified' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'"
                           x-text="result === 'Verified' ? 'Hashes match — file is intact' : 'Hashes DO NOT match — possible tampering'"></p>
                    </div>
                </div>
            </div>

            {{-- Evidence metadata --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Evidence Details</h3>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Case Number</dt>
                    <dd class="mt-0.5 font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $evidence->case_number }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Title</dt>
                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ $evidence->title }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Original Filename</dt>
                    <dd class="mt-0.5 font-mono text-xs text-slate-700 dark:text-slate-300 break-all">{{ $evidence->original_name ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">File Size</dt>
                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ $evidence->file_size_human }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">MIME Type</dt>
                    <dd class="mt-0.5 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $evidence->mime_type ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Uploaded By</dt>
                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ $evidence->uploader?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Uploaded At</dt>
                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ $evidence->created_at->format('d M Y, H:i') }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Evidence ID</dt>
                    <dd class="mt-0.5 font-mono text-xs text-slate-500 dark:text-slate-400 break-all">{{ $evidence->id }}</dd></div>
                </dl>
            </div>
        </div>

        {{-- Hash history --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Hash History</h3>
                <p class="text-xs text-slate-400 mt-0.5">All stored hash records for this evidence (append-only, immutable)</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr>
                        <th>#</th><th>Algorithm</th><th>Hash Value</th><th>Generated At</th><th>Generated By</th>
                    </tr></thead>
                    <tbody>
                    @forelse($hashHistory as $i => $hash)
                    <tr>
                        <td class="text-xs text-slate-400">{{ $i + 1 }}</td>
                        <td><span class="badge badge-blue uppercase">{{ $hash->hash_type }}</span></td>
                        <td>
                            <p class="font-mono text-xs text-slate-600 dark:text-slate-400">{{ substr($hash->hash_value, 0, 20) }}...</p>
                            <details class="mt-1">
                                <summary class="text-xs text-indigo-600 dark:text-indigo-400 cursor-pointer hover:underline select-none">Show full hash</summary>
                                <p class="mt-1 font-mono text-xs text-slate-600 dark:text-slate-400 break-all bg-slate-50 dark:bg-slate-900 rounded p-2">{{ $hash->hash_value }}</p>
                            </details>
                        </td>
                        <td class="text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">
                            {{ $hash->generated_at->format('d M Y, H:i:s') }}
                            <p class="text-xs text-slate-400">{{ $hash->generated_at->diffForHumans() }}</p>
                        </td>
                        <td class="text-sm text-slate-600 dark:text-slate-400">{{ $hash->generatedBy?->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm">No hash records found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Verification audit log --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Verification Audit Log</h3>
                <p class="text-xs text-slate-400 mt-0.5">All integrity checks performed on this evidence</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr>
                        <th>Timestamp</th><th>Performed By</th><th>Event</th><th>Description</th><th>Details</th>
                    </tr></thead>
                    <tbody>
                    @forelse($verificationLogs as $log)
                    <tr>
                        <td class="text-xs text-slate-400 whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                                    {{ strtoupper(substr($log->causer?->name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $log->causer?->name ?? 'System' }}</p>
                                    @if($log->causer)<x-rank-badge :rank="$log->causer->rank" size="xs"/>@endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($log->log_name === 'integrity_alert')
                            <span class="badge badge-red">Tamper Alert</span>
                            @elseif($log->log_name === 'integrity_rehash')
                            <span class="badge badge-purple">Re-hash</span>
                            @else
                            <span class="badge badge-green">Verified</span>
                            @endif
                        </td>
                        <td class="text-sm text-slate-600 dark:text-slate-400 max-w-xs">{{ $log->description }}</td>
                        <td>
                            @if($log->properties->isNotEmpty())
                            <details class="text-xs text-slate-500 cursor-pointer">
                                <summary class="hover:text-indigo-600 dark:hover:text-indigo-400 select-none">View</summary>
                                <pre class="mt-2 bg-slate-50 dark:bg-slate-900 rounded p-2 text-xs overflow-x-auto max-w-xs">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                            </details>
                            @else<span class="text-slate-300 dark:text-slate-600">—</span>@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm">No verification logs yet. Click Verify Now to run the first check.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('integrity.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Integrity Dashboard
            </a>
            <a href="{{ route('evidence.show', $evidence) }}" class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                View Evidence Details
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
    function integrityReport(evidenceId, verifyUrl, rehashUrl, canRehash) {
        return {
            loading:   false,
            rehashing: false,
            result:    'idle',
            message:   'Click Verify Now to run an integrity check.',
            computedHash: null,
            verifiedAt:   null,

            async verify() {
                this.loading = true;
                this.result  = 'loading';
                try {
                    const res = await fetch(verifyUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    this.result       = data.status;
                    this.message      = data.message;
                    this.computedHash = data.computed_hash;
                    this.verifiedAt   = data.verified_at;
                } catch(e) {
                    this.result  = 'idle';
                    alert('Verification failed. Please try again.');
                } finally {
                    this.loading = false;
                }
            },

            async rehash() {
                if (!canRehash) return;
                if (!confirm('Re-generate the stored hash for this evidence? This creates a new hash record and marks the current file as the new baseline. This action is permanently logged.')) return;
                this.rehashing = true;
                try {
                    const res = await fetch(rehashUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert('Hash re-generated successfully. Reloading page...');
                        window.location.reload();
                    } else {
                        alert(data.error || 'Re-hash failed.');
                    }
                } catch(e) {
                    alert('Re-hash request failed. Please try again.');
                } finally {
                    this.rehashing = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>

