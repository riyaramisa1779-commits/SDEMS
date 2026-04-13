<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('Password@123!'),
            'is_active' => true,
            'rank'      => 1,
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Password@123!',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_locked_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'locked_until' => now()->addMinutes(30),
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_failed_login_increments_counter(): void
    {
        $user = User::factory()->create(['failed_login_attempts' => 0]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertEquals(1, $user->fresh()->failed_login_attempts);
    }

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['failed_login_attempts' => 4]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertNotNull($user->fresh()->locked_until);
        $this->assertTrue($user->fresh()->isLocked());
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
