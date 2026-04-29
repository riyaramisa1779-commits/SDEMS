<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Advanced Evidence Search') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-rank-badge :rank="auth()->user()->rank" />
                <span class="text-sm text-gray-600">
                    {{ auth()->user()->name }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="evidenceSearch()">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Search Header --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('search.index') }}" class="space-y-4">
                        
                        {{-- Main Search Bar --}}
                        <div class="relative">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="search" 
                                    x-model="searchTerm"
                                    @input.debounce.300ms="fetchSuggestions()"
                                    placeholder="Search by case number, title, or description..."
                                    value="{{ request('search') }}"
                                    class="w-full rounded-lg border-gray-300 pl-10 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Real-time Suggestions Dropdown --}}
                            <div 
                                x-show="suggestions.length > 0 && showSuggestions" 
                                @click.away="showSuggestions = false"
                                class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg"
                                x-transition
                            >
                                <ul class="max-h-60 overflow-auto rounded-md py-1 text-base ring-1 ring-black ring-opacity-5">
                                    <template x-for="item in suggestions" :key="item.id">
                                        <li>
                                            <a 
                                                :href="`/evidence/${item.id}`"
                                                class="block px-4 py-2 hover:bg-gray-100"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="font-medium text-gray-900" x-text="item.case_number"></span>
                                                        <span class="text-gray-600"> - </span>
                                                        <span class="text-gray-700" x-text="item.title"></span>
                                                    </div>
                                                    <span class="text-xs text-gray-500" x-text="item.category"></span>
                                                </div>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        {{-- Advanced Filters Toggle --}}
                        <div class="flex items-center justify-between">
                            <button 
                                type="button"
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                                <span x-text="showFilters ? 'Hide Filters' : 'Show Advanced Filters'"></span>
                            </button>

                            <div class="flex space-x-2">
                                <a 
                                    href="{{ route('search.index') }}"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                >
                                    Clear Filters
                                </a>
                                <button 
                                    type="submit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Search
                                </button>
                            </div>
                        </div>

                        {{-- Advanced Filters Panel --}}
                        <div x-show="showFilters" x-collapse class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                
                                {{-- Date Range --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date From</label>
                                    <input 
                                        type="date" 
                                        name="date_from" 
                                        value="{{ request('date_from') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date To</label>
                                    <input 
                                        type="date" 
                                        name="date_to" 
                                        value="{{ request('date_to') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <select 
                                        name="category[]" 
                                        multiple
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ in_array($category, (array) request('category', [])) ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple</p>
                                </div>

                                {{-- Tags --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tags</label>
                                    <input 
                                        type="text" 
                                        name="tags" 
                                        value="{{ request('tags') }}"
                                        placeholder="tag1, tag2, tag3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Comma-separated</p>
                                </div>

                                {{-- Uploaded By --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Uploaded By</label>
                                    <select 
                                        name="uploaded_by"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('uploaded_by') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} (Rank {{ $user->rank }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Current Custodian --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Current Custodian</label>
                                    <select 
                                        name="assigned_to"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} (Rank {{ $user->rank }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select 
                                        name="status[]" 
                                        multiple
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        @foreach(['pending', 'active', 'in_review', 'admitted', 'rejected', 'archived'] as $status)
                                            <option value="{{ $status }}" {{ in_array($status, (array) request('status', [])) ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple</p>
                                </div>

                                {{-- Integrity Status --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Integrity Status</label>
                                    <select 
                                        name="integrity_status[]" 
                                        multiple
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="pending" {{ in_array('pending', (array) request('integrity_status', [])) ? 'selected' : '' }}>Pending</option>
                                        <option value="verified" {{ in_array('verified', (array) request('integrity_status', [])) ? 'selected' : '' }}>Verified</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple</p>
                                </div>

                            </div>
                        </div>

                    </form>
                </div>
            </div>

            {{-- Results Count --}}
            @if($evidence->total() > 0)
                <div class="mb-4 text-sm text-gray-600">
                    Found <span class="font-semibold">{{ $evidence->total() }}</span> evidence item(s)
                    @if(request()->hasAny(['search', 'category', 'tags', 'date_from', 'date_to', 'uploaded_by', 'assigned_to', 'status', 'integrity_status']))
                        matching your filters
                    @endif
                </div>
            @endif

            {{-- Results Table --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Case Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Uploaded By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Current Custodian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Integrity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Activity</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($evidence as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->case_number }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if(request('search'))
                                                {!! str_ireplace(request('search'), '<mark class="bg-yellow-200">' . request('search') . '</mark>', e($item->title)) !!}
                                            @else
                                                {{ $item->title }}
                                            @endif
                                        </div>
                                        @if($item->tags)
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach($item->tags as $tag)
                                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                                        {{ $tag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                            {{ ucfirst(str_replace('_', ' ', $item->category)) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $item->uploader->name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    <x-rank-badge :rank="$item->uploader->rank" size="xs" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if($item->custodian)
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->custodian->name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        <x-rank-badge :rank="$item->custodian->rank" size="xs" />
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @php
                                            $integrityStatus = $item->getIntegrityStatus();
                                            $statusColors = [
                                                'Verified' => 'bg-green-100 text-green-800',
                                                'Pending' => 'bg-yellow-100 text-yellow-800',
                                                'Tampered' => 'bg-red-100 text-red-800',
                                                'Missing' => 'bg-gray-100 text-gray-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusColors[$integrityStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $integrityStatus }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ $item->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('evidence.show', $item) }}" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('custody.show', $item) }}" class="text-blue-600 hover:text-blue-900" title="View Chain of Custody">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </a>
                                            @if(auth()->user()->hasMinimumRank(5))
                                                <a href="{{ route('integrity.show', $item) }}" class="text-green-600 hover:text-green-900" title="Verify Integrity">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="mt-4 text-lg font-medium text-gray-900">No evidence found</p>
                                            <p class="mt-2 text-sm text-gray-500">Try adjusting your search filters</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($evidence->hasPages())
                    <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        {{ $evidence->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function evidenceSearch() {
            return {
                searchTerm: '{{ request("search") }}',
                showFilters: {{ request()->hasAny(['category', 'tags', 'date_from', 'date_to', 'uploaded_by', 'assigned_to', 'status', 'integrity_status']) ? 'true' : 'false' }},
                showSuggestions: false,
                suggestions: [],
                loading: false,

                async fetchSuggestions() {
                    if (this.searchTerm.length < 2) {
                        this.suggestions = [];
                        return;
                    }

                    this.loading = true;
                    
                    try {
                        const response = await fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(this.searchTerm)}`);
                        const data = await response.json();
                        this.suggestions = data;
                        this.showSuggestions = true;
                    } catch (error) {
                        console.error('Error fetching suggestions:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
