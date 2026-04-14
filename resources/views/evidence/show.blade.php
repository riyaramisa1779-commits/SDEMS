<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200 truncate max-w-md">
                    {{ $evidence->title }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                {{-- Status badge --}}
                @php
                    $statusColors = [
                        'pending'     => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                        'active'      => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'checked_out' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'sealed'      => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                        'archived'    => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
                        'flagged'     => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                    ];
                    $statusColor = $statusColors[$evidence->status] ?? 'bg-slate-100 text-slate-700';
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColor }}">
                    {{ ucfirst(str_replace('_', ' ', $evidence->status)) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('success'))
            <div class="flex items-start gap-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Left: Preview ──────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Preview panel --}}
                @php
                    $isImage = str_starts_with($evidence->mime_type ?? '', 'image/');
                    $isPdf   = $evidence->mime_type === 'application/pdf';
                    $canPreview = $isImage || $isPdf;
                @endphp

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Preview</h3>
                        <a href="{{ route('evidence.download', $evidence) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download
                        </a>
                    </div>

                    <div class="p-5">
                        @if ($isImage)
                            <div class="flex items-center justify-center bg-slate-100 dark:bg-slate-900 rounded-lg overflow-hidden min-h-48">
                                <img src="{{ route('evidence.preview', $evidence) }}"
                                     alt="{{ $evidence->title }}"
                                     class="max-w-full max-h-96 object-contain rounded">
                            </div>
                        @elseif ($isPdf)
                            <iframe src="{{ route('evidence.preview', $evidence) }}"
                                    class="w-full rounded-lg border border-slate-200 dark:border-slate-700"
                                    style="height: 500px;"
                                    title="{{ $evidence->title }}">
                            </iframe>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ $evidence->original_name ?? 'Evidence File' }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ $evidence->mime_type ?? 'Unknown type' }} &middot; {{ $evidence->file_size_human }}
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-3">
                                    Preview not available for this file type.
                                </p>
                                <a href="{{ route('evidence.download', $evidence) }}"
                                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download File
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                @if ($evidence->description)
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-3">Description</h3>
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $evidence->description }}</p>
                    </div>
                @endif

            </div>

            {{-- ── Right: Metadata + Integrity ────────────────────────────── --}}
            <div class="space-y-5">

                {{-- Metadata --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Details</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Case Number</dt>
                            <dd class="mt-0.5 font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $evidence->case_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Category</dt>
                            <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $evidence->category)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Original Filename</dt>
                            <dd class="mt-0.5 text-slate-700 dark:text-slate-300 break-all text-xs font-mono">{{ $evidence->original_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">File Size</dt>
                            <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ $evidence->file_size_human }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">MIME Type</dt>
                            <dd class="mt-0.5 text-slate-700 dark:text-slate-300 text-xs font-mono">{{ $evidence->mime_type ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Uploaded By</dt>
                            <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ $evidence->uploader->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Uploaded At</dt>
                            <dd class="mt-0.5 text-slate-700 dark:text-slate-300">{{ $evidence->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Version</dt>
                            <dd class="mt-0.5 text-slate-700 dark:text-slate-300">v{{ $evidence->version }}</dd>
                        </div>
                        @if ($evidence->tags)
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Tags</dt>
                                <dd class="flex flex-wrap gap-1.5">
                                    @foreach ($evidence->tags as $tag)
                                        <span class="inline-flex rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 text-xs font-medium px-2 py-0.5">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Integrity / Hash --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Integrity</h3>

                    @if ($evidence->latestHash)
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">
                                    Hash Verified
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">SHA-256</p>
                                <p class="font-mono text-xs text-slate-700 dark:text-slate-300 break-all bg-slate-50 dark:bg-slate-900 rounded-lg p-2 border border-slate-200 dark:border-slate-700">
                                    {{ $evidence->latestHash->hash_value }}
                                </p>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Computed {{ $evidence->latestHash->generated_at->diffForHumans() }}
                            </p>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide">
                                Hash Pending
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            The SHA-256 hash is being calculated in the background. Refresh this page in a moment.
                        </p>
                    @endif
                </div>

                {{-- Latest Custody --}}
                @if ($evidence->latestCustody)
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-3">Latest Custody</h3>
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-xs text-slate-500 dark:text-slate-400">Action</dt>
                                <dd class="font-semibold text-slate-800 dark:text-slate-200 capitalize">{{ $evidence->latestCustody->action }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500 dark:text-slate-400">Custodian</dt>
                                <dd class="text-slate-700 dark:text-slate-300">{{ $evidence->latestCustody->toUser->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500 dark:text-slate-400">Timestamp</dt>
                                <dd class="text-slate-700 dark:text-slate-300">{{ $evidence->latestCustody->timestamp->format('d M Y, H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                {{-- Evidence ID (for reference) --}}
                <div class="rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 px-4 py-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Evidence ID</p>
                    <p class="font-mono text-xs text-slate-700 dark:text-slate-300 break-all">{{ $evidence->id }}</p>
                </div>

            </div>
        </div>

        {{-- Back link --}}
        <div>
            <a href="{{ route('evidence.create') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload another file
            </a>
        </div>

    </div>
</x-app-layout>
