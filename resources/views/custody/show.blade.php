<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('custody.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200 truncate max-w-sm">{{ $evidence->title }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $evidence->case_number }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $sc = ['pending'=>'badge-yellow','active'=>'badge-green','in_review'=>'badge-blue','admitted'=>'badge-purple','rejected'=>'badge-red','archived'=>'badge-gray'];
                @endphp
                <span class="{{ $sc[$evidence->status] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_',' ',$evidence->status)) }}</span>
                <a href="{{ route('evidence.show', $evidence) }}" class="btn-ghost btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    View Evidence
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="custodyShow()">

        {{-- Toast --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="flex items-center gap-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300 animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
            <button @click="show=false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        {{-- Chain Integrity Warning --}}
        @if(!$chainIntact)
        <div class="flex items-start gap-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 px-4 py-3 text-sm text-red-800 dark:text-red-300">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-semibold">Chain Integrity Warning</p>
                <p class="text-xs mt-0.5">The linked-list chain verification failed. A gap or inconsistency was detected. This evidence may not be court-admissible without further investigation.</p>
            </div>
        </div>
        @endif

        @if(count($gaps) > 0)
        <div class="flex items-start gap-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-semibold">Custody Gap Detected</p>
                <p class="text-xs mt-0.5">{{ count($gaps) }} transfer(s) show a discrepancy between the previous custodian and the recorded sender. Highlighted in the timeline below.</p>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Left: Timeline ──────────────────────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Custody Timeline</h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $chain->count() }} event(s) — chronological order</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($chainIntact && count($gaps) === 0)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Chain Intact
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Gap Detected
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        @if($chain->isEmpty())
                        <div class="text-center py-12 text-slate-400 text-sm">No custody records found.</div>
                        @else
                        <div class="relative">
                            {{-- Vertical line --}}
                            <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-slate-200 dark:bg-slate-700"></div>

                            <div class="space-y-0">
                                @foreach($chain as $index => $record)
                                @php
                                    $isGap = in_array($record->id, $gaps);
                                    $actionConfig = [
                                        'upload'   => ['color'=>'bg-slate-500',   'ring'=>'ring-slate-200 dark:ring-slate-700',   'badge'=>'badge-gray',   'label'=>'Upload',   'icon'=>'upload'],
                                        'transfer' => ['color'=>'bg-blue-600',    'ring'=>'ring-blue-100 dark:ring-blue-900/40',  'badge'=>'badge-blue',   'label'=>'Transfer', 'icon'=>'transfer'],
                                        'checkout' => ['color'=>'bg-orange-500',  'ring'=>'ring-orange-100 dark:ring-orange-900/40','badge'=>'badge-orange','label'=>'Checkout','icon'=>'checkout'],
                                        'checkin'  => ['color'=>'bg-emerald-500', 'ring'=>'ring-emerald-100 dark:ring-emerald-900/40','badge'=>'badge-green','label'=>'Check In','icon'=>'checkin'],
                                        'review'   => ['color'=>'bg-cyan-500',    'ring'=>'ring-cyan-100 dark:ring-cyan-900/40',  'badge'=>'badge-blue',   'label'=>'Review',   'icon'=>'review'],
                                        'seal'     => ['color'=>'bg-purple-600',  'ring'=>'ring-purple-100 dark:ring-purple-900/40','badge'=>'badge-purple','label'=>'Seal',    'icon'=>'seal'],
                                        'unseal'   => ['color'=>'bg-yellow-500',  'ring'=>'ring-yellow-100 dark:ring-yellow-900/40','badge'=>'badge-yellow','label'=>'Unseal',  'icon'=>'unseal'],
                                    ];
                                    $cfg = $actionConfig[$record->action] ?? $actionConfig['upload'];
                                @endphp

                                <div class="relative flex gap-4 pb-8 last:pb-0"
                                     x-data="{ expanded: {{ $index === $chain->count()-1 ? 'true' : 'false' }} }">

                                    {{-- Timeline dot --}}
                                    <div class="relative z-10 flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full {{ $cfg['color'] }} ring-4 {{ $cfg['ring'] }} flex items-center justify-center shadow-sm">
                                            @if($cfg['icon'] === 'upload')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            @elseif($cfg['icon'] === 'transfer')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            @elseif($cfg['icon'] === 'checkout')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                                            @elseif($cfg['icon'] === 'checkin')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                                            @elseif($cfg['icon'] === 'review')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            @elseif($cfg['icon'] === 'seal')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            @else
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Content card --}}
                                    <div class="flex-1 min-w-0 {{ $isGap ? 'ring-2 ring-amber-400 dark:ring-amber-500 rounded-xl' : '' }}">
                                        <div class="bg-white dark:bg-slate-800 border {{ $isGap ? 'border-amber-300 dark:border-amber-600' : 'border-slate-200 dark:border-slate-700' }} rounded-xl shadow-sm overflow-hidden">

                                            {{-- Card header --}}
                                            <button @click="expanded = !expanded"
                                                    class="w-full flex items-center justify-between px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="{{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                                                    @if($isGap)
                                                    <span class="badge-yellow text-xs">⚠ Gap</span>
                                                    @endif
                                                    <span class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                                        {{ $record->timestamp->format('d M Y, H:i:s') }}
                                                    </span>
                                                </div>
                                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform duration-200"
                                                     :class="expanded ? 'rotate-180' : ''"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            {{-- Expanded content --}}
                                            <div x-show="expanded"
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 class="px-4 pb-4 border-t border-slate-100 dark:border-slate-700 pt-3 space-y-3">

                                                {{-- From → To --}}
                                                <div class="flex items-center gap-3 flex-wrap">
                                                    {{-- From --}}
                                                    <div class="flex items-center gap-2">
                                                        @if($record->fromUser)
                                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                            {{ strtoupper(substr($record->fromUser->name,0,1)) }}
                                                        </div>
                                                        <div>
                                                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $record->fromUser->name }}</p>
                                                            <x-rank-badge :rank="$record->fromUser->rank" size="xs"/>
                                                        </div>
                                                        @else
                                                        <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 text-xs flex-shrink-0">S</div>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">System</p>
                                                        @endif
                                                    </div>

                                                    {{-- Arrow --}}
                                                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                                    </svg>

                                                    {{-- To --}}
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                            {{ strtoupper(substr($record->toUser->name,0,1)) }}
                                                        </div>
                                                        <div>
                                                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $record->toUser->name }}</p>
                                                            <x-rank-badge :rank="$record->toUser->rank" size="xs"/>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Location --}}
                                                @if($record->location)
                                                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    {{ $record->location }}
                                                </div>
                                                @endif

                                                {{-- Notes --}}
                                                @if($record->notes)
                                                <div class="rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs text-slate-600 dark:text-slate-400 italic">
                                                    "{{ $record->notes }}"
                                                </div>
                                                @endif

                                                {{-- Record ID --}}
                                                <p class="text-xs text-slate-400 font-mono">Record #{{ $record->id }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Right: Evidence Info + Actions ────────────────────────── --}}
            <div class="space-y-5">

                {{-- Evidence Summary --}}
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Evidence Details</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Case</dt>
                            <dd class="font-mono font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $evidence->case_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Category</dt>
                            <dd class="text-slate-700 dark:text-slate-300 mt-0.5">{{ ucfirst(str_replace('_',' ',$evidence->category)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Uploaded By</dt>
                            <dd class="mt-0.5 flex items-center gap-2">
                                <span class="text-slate-700 dark:text-slate-300">{{ $evidence->uploader->name ?? '—' }}</span>
                                @if($evidence->uploader)
                                <x-rank-badge :rank="$evidence->uploader->rank" size="xs"/>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Current Custodian</dt>
                            <dd class="mt-0.5">
                                @if($evidence->custodian)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs">
                                        {{ strtoupper(substr($evidence->custodian->name,0,1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $evidence->custodian->name }}</p>
                                        <x-rank-badge :rank="$evidence->custodian->rank" size="xs"/>
                                    </div>
                                </div>
                                @else
                                <span class="text-slate-400 italic text-xs">Unassigned</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Uploaded</dt>
                            <dd class="text-slate-700 dark:text-slate-300 mt-0.5">{{ $evidence->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                        @if($evidence->latestHash)
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">SHA-256</dt>
                            <dd class="font-mono text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 rounded p-2 border border-slate-200 dark:border-slate-700 break-all">
                                {{ $evidence->latestHash->hash_value }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Actions Panel --}}
                @php
                    $isCustodian = $evidence->assigned_to === auth()->id();
                    $isAdmin = auth()->user()->rank >= 8;
                    $canAct = ($isCustodian || $isAdmin) && !$evidence->isLocked();
                @endphp

                @if($canAct)
                <div class="card p-5 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Custody Actions</h3>

                    {{-- Transfer --}}
                    <button @click="transferModal.open = true"
                            class="w-full flex items-center gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors text-left group">
                        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Transfer Custody</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400">Assign to another officer</p>
                        </div>
                    </button>

                    {{-- Checkout --}}
                    <button @click="checkoutModal.open = true"
                            class="w-full flex items-center gap-3 p-3 rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors text-left">
                        <div class="w-9 h-9 rounded-lg bg-orange-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-orange-800 dark:text-orange-300">Check Out</p>
                            <p class="text-xs text-orange-600 dark:text-orange-400">For court, lab, or review</p>
                        </div>
                    </button>

                    {{-- Checkin --}}
                    <button @click="checkinModal.open = true"
                            class="w-full flex items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors text-left">
                        <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Check In</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Return evidence to storage</p>
                        </div>
                    </button>
                </div>
                @elseif($evidence->isLocked())
                <div class="card p-5">
                    <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold">Evidence Locked</p>
                            <p class="text-xs mt-0.5">Status is <strong>{{ $evidence->status }}</strong>. No custody actions permitted.</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="card p-5">
                    <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold">Read-Only Access</p>
                            <p class="text-xs mt-0.5">You are not the current custodian. Actions are restricted.</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Chain Stats --}}
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-3">Chain Statistics</h3>
                    <div class="grid grid-cols-2 gap-3">
                        @php
                            $transfers = $chain->where('action','transfer')->count();
                            $checkouts = $chain->where('action','checkout')->count();
                            $checkins  = $chain->where('action','checkin')->count();
                        @endphp
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-3 text-center">
                            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $chain->count() }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Total Events</p>
                        </div>
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-3 text-center">
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $transfers }}</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Transfers</p>
                        </div>
                        <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-3 text-center">
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $checkouts }}</p>
                            <p class="text-xs text-orange-500 dark:text-orange-400 mt-0.5">Checkouts</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center">
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $checkins }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Check-ins</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Transfer Modal ──────────────────────────────────────────────────── --}}
    <div x-show="transferModal.open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div x-show="transferModal.open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="transferModal.open = false"
             class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-blue-50 dark:bg-blue-900/20">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Transfer Custody</h3>
                        <p class="text-xs text-slate-500">{{ $evidence->case_number }}</p>
                    </div>
                </div>
                <button @click="transferModal.open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('custody.transfer', $evidence) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 px-4 py-3 text-xs text-amber-800 dark:text-amber-300">
                    <strong>Evidence:</strong> {{ $evidence->title }}
                </div>
                <div>
                    <label class="form-label">Transfer To <span class="text-red-500">*</span></label>
                    <select name="to_user_id" required class="form-input">
                        <option value="">Select recipient officer...</option>
                        @foreach($eligibleUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} — Rank {{ $u->rank }}</option>
                        @endforeach
                    </select>
                    @error('to_user_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Location</label>
                    <input type="text" name="location" placeholder="e.g. Digital Evidence Lab, Room 204" class="form-input" maxlength="255">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" placeholder="Reason for transfer, court reference, etc." class="form-input resize-none" maxlength="2000"></textarea>
                </div>
                <div class="flex items-start gap-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 p-3">
                    <input type="checkbox" name="acknowledged" value="1" id="ack_show" required class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="ack_show" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        I confirm this custody transfer is authorised and I accept legal responsibility. This record is immutable and court-admissible.
                    </label>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="transferModal.open = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary bg-blue-600 hover:bg-blue-700 focus:ring-blue-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/></svg>
                        Confirm Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Checkout Modal ──────────────────────────────────────────────────── --}}
    <div x-show="checkoutModal.open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div x-show="checkoutModal.open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="checkoutModal.open = false"
             class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-orange-50 dark:bg-orange-900/20">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Check Out Evidence</h3>
                </div>
                <button @click="checkoutModal.open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('custody.checkout', $evidence) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="form-label">Checkout Location <span class="text-red-500">*</span></label>
                    <input type="text" name="location" required placeholder="e.g. Courtroom 3, Forensics Lab" class="form-input" maxlength="255">
                    @error('location')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Purpose of checkout, case reference..." class="form-input resize-none" maxlength="2000"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="checkoutModal.open = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                        Check Out
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Check-In Modal ──────────────────────────────────────────────────── --}}
    <div x-show="checkinModal.open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div x-show="checkinModal.open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="checkinModal.open = false"
             class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-emerald-50 dark:bg-emerald-900/20">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Check In Evidence</h3>
                </div>
                <button @click="checkinModal.open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('custody.checkin', $evidence) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="form-label">Return Location <span class="text-red-500">*</span></label>
                    <input type="text" name="location" required placeholder="e.g. Evidence Storage Room B" class="form-input" maxlength="255">
                    @error('location')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Condition on return, any observations..." class="form-input resize-none" maxlength="2000"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="checkinModal.open = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                        Check In
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function custodyShow() {
        return {
            transferModal: { open: false },
            checkoutModal: { open: false },
            checkinModal:  { open: false },
        };
    }
    </script>
    @endpush
</x-app-layout>
