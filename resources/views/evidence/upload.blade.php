<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Upload Evidence</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Success message --}}
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 px-4 py-3 text-sm text-red-800 dark:text-red-300">
                <p class="font-medium mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('evidence.store') }}"
              enctype="multipart/form-data"
              id="evidence-form">
            @csrf

            <div class="space-y-6">

                {{-- ── File Upload ──────────────────────────────────────────── --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
                     x-data="dropZone()">

                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">
                        File Upload
                    </h3>

                    {{-- Drop zone --}}
                    <div class="border-2 border-dashed rounded-xl transition-all duration-200 cursor-pointer"
                         :class="isDragging
                             ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20'
                             : 'border-slate-300 dark:border-slate-600 hover:border-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         @click="$refs.fileInput.click()">

                        <div class="flex flex-col items-center justify-center py-12 px-6 text-center pointer-events-none">
                            <svg class="w-12 h-12 mb-4"
                                 :class="isDragging ? 'text-blue-500' : 'text-slate-400 dark:text-slate-500'"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Drag a file here or <span class="text-blue-600 dark:text-blue-400">click to browse</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Images, PDFs, documents, videos, audio, archives — max 2 GB
                            </p>
                        </div>

                        <input type="file"
                               name="file"
                               id="file-input"
                               x-ref="fileInput"
                               class="hidden"
                               @change="handleFileSelect($event)"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,
                                       .jpg,.jpeg,.png,.gif,.webp,.tiff,.bmp,
                                       .mp4,.avi,.mov,.mkv,.webm,
                                       .mp3,.wav,.ogg,.m4a,
                                       .zip,.tar,.gz,.7z">
                    </div>

                    {{-- Selected file display --}}
                    <div x-show="selectedFile" x-cloak class="mt-4">
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 px-4 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="shrink-0 w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate" x-text="selectedFile?.name"></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="formatBytes(selectedFile?.size)"></p>
                                </div>
                            </div>
                            <button type="button"
                                    @click.stop="removeFile()"
                                    class="ml-3 shrink-0 text-slate-400 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Client-side file error --}}
                    <p x-show="fileError" x-cloak x-text="fileError"
                       class="mt-2 text-xs text-red-600 dark:text-red-400"></p>

                    @error('file')
                        <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Metadata ─────────────────────────────────────────────── --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">
                        Evidence Metadata
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                        {{-- Case Number --}}
                        <div>
                            <label for="case_number" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Case Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="case_number"
                                   name="case_number"
                                   value="{{ old('case_number') }}"
                                   placeholder="e.g. CASE-2026-001"
                                   class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors
                                          {{ $errors->has('case_number') ? 'border-red-400' : 'border-slate-300 dark:border-slate-600' }}">
                            @error('case_number')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="category" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="category"
                                    name="category"
                                    class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors
                                           {{ $errors->has('category') ? 'border-red-400' : 'border-slate-300 dark:border-slate-600' }}">
                                <option value="">Select a category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $cat)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Title --}}
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="Brief descriptive title for this evidence"
                                   class="w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors
                                          {{ $errors->has('title') ? 'border-red-400' : 'border-slate-300 dark:border-slate-600' }}">
                            @error('title')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Description <span class="text-slate-400 font-normal">(optional)</span>
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Describe the evidence, its context, and relevance to the case"
                                      class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-none">{{ old('description') }}</textarea>
                        </div>

                        {{-- Tags --}}
                        <div class="sm:col-span-2" x-data="tagInput()">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Tags <span class="text-slate-400 font-normal">(optional)</span>
                            </label>
                            <div class="flex flex-wrap gap-2 mb-2" x-show="tags.length > 0" x-cloak>
                                <template x-for="(tag, index) in tags" :key="index">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 text-xs font-medium px-2.5 py-1">
                                        <span x-text="tag"></span>
                                        <button type="button" @click="removeTag(index)" class="ml-0.5 text-blue-500 hover:text-blue-700">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <input type="text"
                                   x-model="tagInput"
                                   @keydown.enter.prevent="addTag()"
                                   @keydown.comma.prevent="addTag()"
                                   placeholder="Type a tag and press Enter or comma to add"
                                   class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            <input type="hidden" name="tags" :value="tags.join(',')">
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Press Enter or comma to add a tag</p>
                        </div>

                    </div>
                </div>

                {{-- ── Security Notice ──────────────────────────────────────── --}}
                <div class="flex items-start gap-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 px-4 py-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs text-amber-800 dark:text-amber-300">
                        <span class="font-semibold">Chain of Custody Notice:</span>
                        Uploading this file will create an immutable audit record under your account.
                        A SHA-256 hash will be computed and stored for integrity verification.
                        This action is logged and cannot be undone.
                    </p>
                </div>

                {{-- ── Submit ───────────────────────────────────────────────── --}}
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('dashboard') }}"
                       class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                        ← Back to Dashboard
                    </a>

                    <button type="submit"
                            form="evidence-form"
                            class="inline-flex items-center gap-2 rounded-lg px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Evidence
                    </button>
                </div>

            </div>
        </form>
    </div>

    @push('scripts')
    @php
        $existingTags = old('tags')
            ? array_values(array_filter(array_map('trim', explode(',', old('tags')))))
            : [];
    @endphp
    <script>
    function dropZone() {
        return {
            isDragging: false,
            selectedFile: null,
            fileError: '',
            maxSize: 2 * 1024 * 1024 * 1024,

            handleDrop(event) {
                this.isDragging = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    this.validateAndSet(file);
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.fileInput.files = dt.files;
                }
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) this.validateAndSet(file);
            },

            validateAndSet(file) {
                this.fileError = '';
                if (file.size > this.maxSize) {
                    this.fileError = 'File too large. Maximum size is 2 GB.';
                    this.selectedFile = null;
                    return;
                }
                this.selectedFile = file;
            },

            removeFile() {
                this.selectedFile = null;
                this.fileError = '';
                this.$refs.fileInput.value = '';
            },

            formatBytes(bytes) {
                if (!bytes) return '0 B';
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                if (bytes < 1073741824) return (bytes / 1048576).toFixed(1) + ' MB';
                return (bytes / 1073741824).toFixed(2) + ' GB';
            },
        };
    }

    function tagInput() {
        return {
            tags: {{ json_encode($existingTags) }},
            tagInput: '',
            addTag() {
                const tag = this.tagInput.trim().replace(/,+$/, '');
                if (tag && !this.tags.includes(tag) && this.tags.length < 20) {
                    this.tags.push(tag);
                }
                this.tagInput = '';
            },
            removeTag(index) {
                this.tags.splice(index, 1);
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
