<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin',       'guard_name' => 'web']);
        Role::create(['name' => 'user',        'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create(['rank' => 10, 'is_active' => true]);
        $this->superAdmin->assignRole('super-admin');

        $this->admin = User::factory()->create(['rank' => 8, 'is_active' => true]);
        $this->admin->assignRole('admin');

        $this->regularUser = User::factory()->create(['rank' => 1, 'is_active' => true]);
        $this->regularUser->assignRole('user');
    }

    public function test_admin_can_view_user_list(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertStatus(200);
    }

    public function test_regular_user_cannot_access_admin_area(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name'      => 'New User',
                'email'     => 'newuser@test.com',
                'rank'      => 1,
                'role'      => 'user',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_admin_cannot_assign_rank_higher_than_own(): void
    {
        $this->actingAs($this->admin) // rank 8
            ->post(route('admin.users.store'), [
                'name'  => 'High Rank User',
                'email' => 'highrank@test.com',
                'rank'  => 9, // higher than admin's rank 8
                'role'  => 'user',
            ])
            ->assertSessionHasErrors('rank');
    }

    public function test_admin_can_soft_delete_user(): void
    {
        $target = User::factory()->create(['rank' => 1]);
        $target->assignRole('user');

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $target = User::factory()->create(['rank' => 1, 'is_active' => true]);
        $target->assignRole('user');

        $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $target))
            ->assertRedirect();

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_super_admin_can_restore_deleted_user(): void
    {
        $target = User::factory()->create(['rank' => 1]);
        $target->assignRole('user');
        $target->delete();

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.restore', $target->id))
            ->assertRedirect(route('admin.users.index'));

        $this->assertNotSoftDeleted('users', ['id' => $target->id]);
    }
}
