<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Chain of Custody — Evidence Registry</h2>
            </div>
            @if(auth()->user()->rank >= 3)
            <a href="{{ route('evidence.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Evidence
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="custodyIndex()">

        {{-- Toast --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="flex items-center gap-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300 animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="flex-1">{{ session('success') }}</span>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <form method="GET" action="{{ route('custody.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wide">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Title or case number..."
                           class="form-input text-sm"/>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wide">Status</label>
                    <select name="status" class="form-input text-sm">
                        <option value="">All Statuses</option>
                        @foreach(['pending','active','in_review','admitted','rejected','archived'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_',' ',$s)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search','status','case_number']))
                    <a href="{{ route('custody.index') }}" class="btn-secondary btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Evidence Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Evidence Items</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $evidence->total() }} item(s) found</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Title</th>
                            <th>Current Custodian</th>
                            <th>Status</th>
                            <th>Chain Events</th>
                            <th>Last Action</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evidence as $item)
                        @php
                            $statusColors = [
                                'pending'   => 'badge-yellow',
                                'active'    => 'badge-green',
                                'in_review' => 'badge-blue',
                                'admitted'  => 'badge-purple',
                                'rejected'  => 'badge-red',
                                'archived'  => 'badge-gray',
                            ];
                            $sc = $statusColors[$item->status] ?? 'badge-gray';
                            $isCustodian = $item->assigned_to === auth()->id();
                            $isAdmin     = auth()->user()->rank >= 8;
                            $canAct      = ($isCustodian || $isAdmin) && !$item->isLocked();
                        @endphp
                        <tr>
                            {{-- Case Number --}}
                            <td>
                                <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">
                                    {{ $item->case_number }}
                                </span>
                            </td>

                            {{-- Title --}}
                            <td>
                                <div class="max-w-xs">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">{{ $item->title }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ ucfirst(str_replace('_',' ',$item->category)) }}</p>
                                </div>
                            </td>

                            {{-- Current Custodian --}}
                            <td>
                                @if($item->custodian)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($item->custodian->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->custodian->name }}</p>
                                        <x-rank-badge :rank="$item->custodian->rank" size="xs"/>
                                    </div>
                                </div>
                                @else
                                <span class="text-xs text-slate-400 italic">Unassigned</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="{{ $sc }}">{{ ucfirst(str_replace('_',' ',$item->status)) }}</span>
                            </td>

                            {{-- Chain Events --}}
                            <td>
                                <span class="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400">
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    {{ $item->custody_chain_count }} event(s)
                                </span>
                            </td>

                            {{-- Last Action --}}
                            <td>
                                @if($item->latestCustody)
                                <div>
                                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300 capitalize">{{ $item->latestCustody->action }}</span>
                                    <p class="text-xs text-slate-400">{{ $item->latestCustody->timestamp->diffForHumans() }}</p>
                                </div>
                                @else
                                <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- View Chain --}}
                                    <a href="{{ route('custody.show', $item) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Chain
                                    </a>

                                    @if($canAct)
                                    {{-- Transfer --}}
                                    <button @click="openTransfer('{{ $item->id }}', '{{ addslashes($item->title) }}', '{{ $item->case_number }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        Transfer
                                    </button>

                                    {{-- Checkout --}}
                                    <button @click="openCheckout('{{ $item->id }}', '{{ addslashes($item->title) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Checkout
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">No evidence found</p>
                                    <p class="text-xs text-slate-400">Try adjusting your filters or upload new evidence.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($evidence->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $evidence->links() }}
            </div>
            @endif
        </div>

        {{-- ── Transfer Modal ──────────────────────────────────────────────── --}}
        <div x-show="transferModal.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="transferModal.open = false">

            <div x-show="transferModal.open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="transferModal.open = false"
                 class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Transfer Custody</h3>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="transferModal.caseNumber"></p>
                        </div>
                    </div>
                    <button @click="transferModal.open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form :action="'/custody/' + transferModal.evidenceId + '/transfer'" method="POST" class="px-6 py-5 space-y-4">
                    @csrf

                    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3">
                        <p class="text-xs text-blue-800 dark:text-blue-300">
                            <span class="font-semibold">Transferring:</span>
                            <span x-text="transferModal.title" class="ml-1"></span>
                        </p>
                    </div>

                    <div>
                        <label class="form-label">Transfer To <span class="text-red-500">*</span></label>
                        <select name="to_user_id" required
                                class="form-input"
                                x-model="transferModal.toUserId">
                            <option value="">Select recipient officer...</option>
                            @foreach($eligibleUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} — Rank {{ $u->rank }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Location</label>
                        <input type="text" name="location" placeholder="e.g. Evidence Room B, Court Room 3"
                               class="form-input"/>
                    </div>

                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" placeholder="Reason for transfer, court reference, etc."
                                  class="form-input resize-none"></textarea>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 px-4 py-3">
                        <input type="checkbox" name="acknowledged" value="1" id="ack-transfer" required
                               class="mt-0.5 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"/>
                        <label for="ack-transfer" class="text-xs text-amber-800 dark:text-amber-300 cursor-pointer">
                            I confirm this custody transfer is authorised and I accept legal responsibility for this action. This record is immutable and court-admissible.
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="transferModal.open = false" class="btn-secondary btn-sm">Cancel</button>
                        <button type="submit" class="btn-primary btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/>
                            </svg>
                            Confirm Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Checkout Modal ──────────────────────────────────────────────── --}}
        <div x-show="checkoutModal.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="checkoutModal.open = false">

            <div x-show="checkoutModal.open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.outside="checkoutModal.open = false"
                 class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Check Out Evidence</h3>
                    </div>
                    <button @click="checkoutModal.open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form :action="'/custody/' + checkoutModal.evidenceId + '/checkout'" method="POST" class="px-6 py-5 space-y-4">
                    @csrf
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Checking out: <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="checkoutModal.title"></span>
                    </p>
                    <div>
                        <label class="form-label">Location / Purpose <span class="text-red-500">*</span></label>
                        <input type="text" name="location" required placeholder="e.g. Court Room 3, Forensic Lab"
                               class="form-input"/>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Purpose of checkout, reference number..."
                                  class="form-input resize-none"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-1">
                        <button type="button" @click="checkoutModal.open = false" class="btn-secondary btn-sm">Cancel</button>
                        <button type="submit" class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white focus:ring-amber-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                            </svg>
                            Check Out
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function custodyIndex() {
        return {
            transferModal: { open: false, evidenceId: '', title: '', caseNumber: '', toUserId: '' },
            checkoutModal: { open: false, evidenceId: '', title: '' },

            openTransfer(id, title, caseNumber) {
                this.transferModal = { open: true, evidenceId: id, title: title, caseNumber: caseNumber, toUserId: '' };
            },
            openCheckout(id, title) {
                this.checkoutModal = { open: true, evidenceId: id, title: title };
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
