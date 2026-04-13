<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserImportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Admin User Management Controller
 * Protected by: role:admin + rank:8 middleware
 */
class UserController extends Controller
{
    public function __construct(protected UserImportExportService $importExport) {}

    /**
     * List users with search, filter, and pagination.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::withTrashed()->with('roles');

        // Search
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by status
        if ($request->input('status') === 'active') {
            $query->where('is_active', true)->whereNull('deleted_at');
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false)->whereNull('deleted_at');
        } elseif ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        }

        // Filter by rank
        if ($rank = $request->input('rank')) {
            $query->where('rank', (int) $rank);
        }

        $users = $query->orderBy('rank', 'desc')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $authUser = $request->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'rank'     => 'required|integer|between:1,10',
            'role'     => 'required|string|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        // Prevent rank escalation
        if ($validated['rank'] > $authUser->rank && ! $authUser->hasRole('super-admin')) {
            return back()->withErrors(['rank' => 'You cannot assign a rank higher than your own.']);
        }

        $tempPassword = Str::random(16);

        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($tempPassword),
            'rank'               => $validated['rank'],
            'is_active'          => $validated['is_active'] ?? true,
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays(90),
        ]);

        $user->assignRole($validated['role']);

        activity('user_management')
            ->causedBy($authUser)
            ->performedOn($user)
            ->withProperties(['role' => $validated['role'], 'rank' => $validated['rank']])
            ->log('Admin created user');

        return redirect()->route('admin.users.index')
            ->with('success', "User created. Temporary password: {$tempPassword}");
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $authUser = $request->user();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'rank'      => 'required|integer|between:1,10',
            'role'      => 'required|string|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        // Prevent rank escalation
        if ($validated['rank'] > $authUser->rank && ! $authUser->hasRole('super-admin')) {
            return back()->withErrors(['rank' => 'You cannot assign a rank higher than your own.']);
        }

        $oldRank = $user->rank;

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'rank'      => $validated['rank'],
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ]);

        $user->syncRoles([$validated['role']]);

        $properties = ['role' => $validated['role']];
        if ($oldRank !== $validated['rank']) {
            $properties['rank_changed'] = ['from' => $oldRank, 'to' => $validated['rank']];
        }

        activity('user_management')
            ->causedBy($authUser)
            ->performedOn($user)
            ->withProperties($properties)
            ->log('Admin updated user');

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Soft delete a user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        activity('user_management')
            ->causedBy(request()->user())
            ->performedOn($user)
            ->log('Admin soft-deleted user');

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('restore', $user);

        $user->restore();

        activity('user_management')
            ->causedBy(request()->user())
            ->performedOn($user)
            ->log('Admin restored user');

        return redirect()->route('admin.users.index')
            ->with('success', 'User restored.');
    }

    /**
     * Toggle user active/inactive status.
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        activity('user_management')
            ->causedBy(request()->user())
            ->performedOn($user)
            ->withProperties(['status' => $status])
            ->log("Admin {$status} user");

        return back()->with('success', "User {$status}.");
    }

    /**
     * Export users as CSV download.
     */
    public function exportCsv()
    {
        $this->authorize('viewAny', User::class);

        $csv = $this->importExport->exportCsv();

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    /**
     * Import users from CSV upload.
     */
    public function importCsv(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $result = $this->importExport->importCsv(
            $request->file('csv_file'),
            $request->user()
        );

        $message = "Imported {$result['imported']} users.";
        if (! empty($result['errors'])) {
            $message .= ' Errors: ' . implode(' | ', $result['errors']);
        }

        return back()->with('success', $message);
    }
}
