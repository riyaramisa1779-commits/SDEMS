<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Admin Role & Permission Management Controller
 * Protected by: role:super-admin middleware
 */
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        activity('role_management')
            ->causedBy($request->user())
            ->performedOn($role)
            ->withProperties(['permissions' => $validated['permissions'] ?? []])
            ->log('Role created');

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        $permissions    = Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]);
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        activity('role_management')
            ->causedBy($request->user())
            ->performedOn($role)
            ->withProperties(['permissions' => $validated['permissions'] ?? []])
            ->log('Role permissions updated');

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        // Protect built-in roles
        if (in_array($role->name, ['super-admin', 'admin', 'user'])) {
            return back()->withErrors(['role' => 'Cannot delete a built-in role.']);
        }

        $role->delete();

        activity('role_management')
            ->causedBy(request()->user())
            ->log("Role '{$role->name}' deleted");

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted.');
    }
}
