<x-admin-layout title="User Management">
<div x-data="userTable()" x-init="init()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">User Management</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage system users, roles, and access levels</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.users.export-csv') }}" class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
        <a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New User
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-5"><div class="card-body">
<form method="GET" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="form-label">Search</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…" class="form-input pl-9"/>
        </div>
    </div>
    <div class="w-36">
        <label class="form-label">Status</label>
        <select name="status" class="form-input">
            <option value="">All Status</option>
            <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="deleted" {{ request('status')=='deleted' ? 'selected' : '' }}>Deleted</option>
        </select>
    </div>
    <div class="w-32">
        <label class="form-label">Rank</label>
        <select name="rank" class="form-input">
            <option value="">All Ranks</option>
            @foreach(range(1,10) as $r)
            <option value="{{ $r }}" @if(request('rank')==$r) selected @endif>Rank {{ $r }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2 pb-0.5">
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary btn-sm">Reset</a>
    </div>
</form>
</div></div>

{{-- CSV Import panel --}}
<div x-show="showImport" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="card mb-5 border-dashed border-2 border-indigo-300 dark:border-indigo-700">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.import-csv') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-4">
            @csrf
            <div class="flex-1 min-w-48">
                <label class="form-label">CSV File</label>
                <input type="file" name="csv_file" accept=".csv,.txt" class="form-input file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
            </div>
            <div class="flex gap-2 items-end pb-0.5">
                <button type="submit" class="btn-success btn-sm">Upload & Import</button>
                <button type="button" @click="showImport=false" class="btn-secondary btn-sm">Cancel</button>
            </div>
        </form>
        <p class="text-xs text-slate-400 mt-2">CSV format: <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">name, email, rank</code> — one user per line.</p>
    </div>
</div>

{{-- Bulk actions --}}
<div x-show="selected.length > 0" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
     class="card mb-4 border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20">
    <div class="card-body py-3 flex items-center gap-4">
        <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300"><span x-text="selected.length"></span> user(s) selected</span>
        <div class="flex gap-2 ml-auto">
            <button @click="selected=[]" class="btn-ghost btn-sm text-slate-500">Deselect all</button>
            <button class="btn-danger btn-sm">Delete Selected</button>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="card-header">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
            All Users <span class="ml-2 text-xs font-normal text-slate-400">({{ $users->total() }} total)</span>
        </h2>
        <button @click="showImport=!showImport" class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import CSV
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead><tr>
                <th class="w-10"><input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"/></th>
                <th>User</th><th>Rank</th><th>Role</th><th>Status</th><th>2FA</th><th>Joined</th><th class="text-right">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($users as $user)
            <tr :class="selected.includes({{ $user->id }}) ? 'bg-indigo-50 dark:bg-indigo-900/20' : '">
                <td><input type="checkbox" :value="{{ $user->id }}" x-model="selected" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"/></td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full {{ $user->rank >= 8 ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : ($user->rank >= 5 ? 'bg-gradient-to-br from-blue-500 to-cyan-600' : 'bg-slate-400') }} flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($user->name,0,1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-800 dark:text-slate-100 text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td><x-rank-badge :rank="$user->rank"/></td>
                <td><span class="badge badge-gray">{{ $user->getRoleNames()->first() ?? '—' }}</span></td>
                <td>
                    @if($user->trashed()) <span class="badge badge-red">Deleted</span>
                    @elseif($user->isLocked()) <span class="badge badge-yellow">Locked</span>
                    @elseif($user->is_active) <span class="badge badge-green"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 inline-block"></span>Active</span>
                    @else <span class="badge badge-gray">Inactive</span>
                    @endif
                </td>
                <td>
                    @if($user->hasTwoFactorEnabled())
                        <span class="text-emerald-500 text-xs font-medium">✓ On</span>
                    @else
                        <span class="text-slate-400 text-xs">Off</span>
                    @endif
                </td>
                <td class="text-xs text-slate-400 whitespace-nowrap">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        @if($user->trashed())
                            <form method="POST" action="{{ route('admin.users.restore',$user->id) }}">@csrf
                                <button type="submit" class="btn-ghost btn-sm text-emerald-600">Restore</button>
                            </form>
                        @else
                            <a href="{{ route('admin.users.edit',$user) }}" class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle-status',$user) }}">@csrf @method('PATCH')
                                <button type="submit" class="btn-ghost btn-sm {{ $user->is_active ? 'text-amber-500' : 'text-emerald-500' }}" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $user->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/></svg>
                                </button>
                            </form>
                            @can('delete',$user)
                            <button @click="confirmDelete({{ $user->id }},'{{ addslashes($user->name) }}')" class="btn-ghost btn-sm text-red-500" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-sm text-slate-500">No users found</p>
                    <a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm">Create first user</a>
                </div>
            </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">{{ $users->links() }}</div>
    @endif
</div>

{{-- Delete modal --}}
<div x-show="deleteModal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="absolute inset-0 bg-black/60" @click="deleteModal.show=false"></div>
    <div class="relative card w-full max-w-md p-6 shadow-2xl animate-slide-down">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Delete User</h3>
                <p class="text-sm text-slate-500 mt-0.5">Delete <strong x-text="deleteModal.name"></strong>? This can be undone by an admin.</p>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <button @click="deleteModal.show=false" class="btn-secondary">Cancel</button>
            <form :action="'/admin/users/' + deleteModal.id" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">Delete User</button>
            </form>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

<script>
function userTable() {
    return {
        selected: [],
        showImport: false,
        deleteModal: { show: false, id: null, name: "" },
        init() {},
        toggleAll(e) {
            this.selected = e.target.checked ? [{{ $users->pluck("id")->implode(",") }}] : [];
        },
        confirmDelete(id, name) {
            this.deleteModal = { show: true, id, name };
        }
    }
}
</script>

</x-admin-layout>


