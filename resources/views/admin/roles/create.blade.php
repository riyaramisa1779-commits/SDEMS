<x-admin-layout title="Create Role">
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.roles.index') }}" class="btn-ghost btn-sm text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>Back
        </a>
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Create Role</h1>
    </div>
    <div class="card">
        <div class="card-header"><h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Role Details</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="form-label">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input {{ $errors->has('name') ? 'form-input-error' : '' }}" placeholder="e.g. investigator"/>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Permissions</label>
                    @foreach($permissions as $group => $perms)
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 pb-1 border-b border-slate-100 dark:border-slate-700">{{ $group }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($perms as $perm)
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" {{ in_array($perm->name, old('permissions',[]))?'checked':' ' }} class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"/>
                                <span class="font-mono text-xs">{{ $perm->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Create Role</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>


