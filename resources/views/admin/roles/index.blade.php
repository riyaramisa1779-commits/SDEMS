<x-admin-layout title="Roles & Permissions">
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Roles & Permissions</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage access control roles and their permissions</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn-primary btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Role
    </a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($roles as $role)
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg {{ $role->name === 'super-admin' ? 'bg-purple-600' : ($role->name === 'admin' ? 'bg-indigo-600' : 'bg-slate-500') }} flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-100">{{ ucfirst($role->name) }}</h3>
                    <p class="text-xs text-slate-400">{{ $role->users()->count() }} user(s)</p>
                </div>
            </div>
            <div class="flex gap-1">
                <a href="{{ route('admin.roles.edit',$role) }}" class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400">Edit</a>
                @if(!in_array($role->name,['super-admin','admin','user']))
                <form method="POST" action="{{ route('admin.roles.destroy',$role) }}" onsubmit="return confirm('Delete role?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-ghost btn-sm text-red-500">Delete</button>
                </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Permissions ({{ $role->permissions->count() }})</p>
            <div class="flex flex-wrap gap-1.5">
                @forelse($role->permissions as $perm)
                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">{{ $perm->name }}</span>
                @empty
                <span class="text-xs text-slate-400">No permissions assigned</span>
                @endforelse
            </div>
        </div>
    </div>
    @endforeach
</div>
</x-admin-layout>
