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

        // ── Senior Investigator (rank 3) ───────────────────────────────────────
        $investigatorRole = Role::firstOrCreate(['name' => 'investigator', 'guard_name' => 'web']);
        $investigatorRole->syncPermissions(['profile.edit']);

        $investigator = User::firstOrCreate(
            ['email' => 'investigator@sdems.local'],
            [
                'name'               => 'Senior Investigator',
                'password'           => Hash::make('Invest@SDEMS#2024!'),
                'rank'               => 3,
                'is_active'          => true,
                'email_verified_at'  => now(),
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90),
            ]
        );
        $investigator->assignRole('investigator');

        // ── Riya — Senior Investigator (rank 3) ────────────────────────────────
        // Dedicated user for the /profile/riya_profile special page.
        $riya = User::firstOrCreate(
            ['email' => 'riya@sdems.local'],
            [
                'name'               => 'Riya',
                'password'           => Hash::make('Riya@SDEMS#2024!'),
                'rank'               => 3,
                'is_active'          => true,
                'email_verified_at'  => now(),
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90),
            ]
        );
        $riya->assignRole('investigator');

        // ── Nusrath — Senior Investigator (rank 3) ─────────────────────────────
        // Dedicated user for the /dashboard/nusrath special page.
        $nusrath = User::firstOrCreate(
            ['email' => 'nusrath@sdems.local'],
            [
                'name'               => 'Nusrath',
                'password'           => Hash::make('Nusrath@SDEMS#2024!'),
                'rank'               => 3,
                'is_active'          => true,
                'email_verified_at'  => now(),
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90),
            ]
        );
        $nusrath->assignRole('investigator');

        // ── Legal Consultant / Auditor (rank 5) ────────────────────────────────
        $auditorRole = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditorRole->syncPermissions(['profile.edit', 'activity-log.view']);

        $auditor = User::firstOrCreate(
            ['email' => 'auditor@sdems.local'],
            [
                'name'               => 'Legal Consultant',
                'password'           => Hash::make('Audit@SDEMS#2024!'),
                'rank'               => 5,
                'is_active'          => true,
                'email_verified_at'  => now(),
                'password_changed_at' => now(),
                'password_expires_at' => now()->addDays(90),
            ]
        );
        $auditor->assignRole('auditor');

        $this->command->info('✅ Roles, permissions, and default users seeded.');
        $this->command->table(
            ['Email', 'Role', 'Rank', 'Password'],
            [
                ['superadmin@sdems.local',  'super-admin',  10, 'Admin@SDEMS#2024!'],
                ['admin@sdems.local',       'admin',         8, 'Admin@SDEMS#2024!'],
                ['auditor@sdems.local',     'auditor',       5, 'Audit@SDEMS#2024!'],
                ['riya@sdems.local',        'investigator',  3, 'Riya@SDEMS#2024!'],
                ['nusrath@sdems.local',     'investigator',  3, 'Nusrath@SDEMS#2024!'],
                ['investigator@sdems.local','investigator',  3, 'Invest@SDEMS#2024!'],
                ['user@sdems.local',        'user',          1, 'User@SDEMS#2024!'],
            ]
        );

        // ── Module 2: Evidence Core ────────────────────────────────────────────
        $this->call(EvidenceSeeder::class);
    }
}
