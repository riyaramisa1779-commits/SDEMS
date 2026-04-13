<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\EvidenceSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Permissions ───────────────────────────────────────────────────────
        $permissions = [
            // User management
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.restore',
            // Role management
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            // Activity log
            'activity-log.view',
            // Profile
            'profile.edit',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles ─────────────────────────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $user       = Role::firstOrCreate(['name' => 'user',        'guard_name' => 'web']);

        // Super-admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());

        // Admin gets user/activity management
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.restore',
            'activity-log.view', 'profile.edit',
        ]);

        // Regular user gets profile only
        $user->syncPermissions(['profile.edit']);

        // ── Super Admin User (rank 10) ─────────────────────────────────────────
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@sdems.local'],
            [
                'name'               => 'Super Admin',
                'password'           => Hash::make('Admin@SDEMS#2024!'),
                'rank'               => 10,
                'is_active'          => true,
                'email_verified_at'  => now(),
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90),
            ]
        );
        $superAdminUser->assignRole('super-admin');

        // ── Admin User (rank 8) ────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@sdems.local'],
            [
                'name'               => 'Admin User',
                'password'           => Hash::make('Admin@SDEMS#2024!'),
                'rank'               => 8,
                'is_active'          => true,
                'email_verified_at'  => now(),
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90),
            ]
        );
        $adminUser->assignRole('admin');

        // ── Regular User (rank 1) ──────────────────────────────────────────────
        $regularUser = User::firstOrCreate(
            ['email' => 'user@sdems.local'],
            [
                'name'               => 'Regular User',
                'password'           => Hash::make('User@SDEMS#2024!'),
                'rank'               => 1,
                'is_active'          => true,
                'email_verified_at'  => now(),
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90),
            ]
        );
        $regularUser->assignRole('user');

        $this->command->info('✅ Roles, permissions, and default users seeded.');
        $this->command->table(
            ['Email', 'Role', 'Rank', 'Password'],
            [
                ['superadmin@sdems.local', 'super-admin', 10, 'Admin@SDEMS#2024!'],
                ['admin@sdems.local',      'admin',        8, 'Admin@SDEMS#2024!'],
                ['user@sdems.local',       'user',         1, 'User@SDEMS#2024!'],
            ]
        );

        // ── Module 2: Evidence Core ────────────────────────────────────────────
        $this->call(EvidenceSeeder::class);
    }
}
