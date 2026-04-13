<?php

namespace Tests\Unit;

use App\Models\PasswordHistory;
use App\Models\User;
use App\Services\PasswordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordServiceTest extends TestCase
{
    use RefreshDatabase;

    private PasswordService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PasswordService();
    }

    public function test_valid_strong_password_passes(): void
    {
        $user   = User::factory()->create();
        $errors = $this->service->validate('StrongPass@123!', $user);
        $this->assertEmpty($errors);
    }

    public function test_short_password_fails(): void
    {
        $user   = User::factory()->create();
        $errors = $this->service->validate('Short@1!', $user);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('12 characters', $errors[0]);
    }

    public function test_password_without_uppercase_fails(): void
    {
        $user   = User::factory()->create();
        $errors = $this->service->validate('lowercase@123!!', $user);
        $this->assertNotEmpty($errors);
    }

    public function test_password_without_special_char_fails(): void
    {
        $user   = User::factory()->create();
        $errors = $this->service->validate('NoSpecialChar123', $user);
        $this->assertNotEmpty($errors);
    }

    public function test_reused_password_fails(): void
    {
        $user = User::factory()->create();

        PasswordHistory::create([
            'user_id'  => $user->id,
            'password' => Hash::make('OldPassword@123!'),
        ]);

        $errors = $this->service->validate('OldPassword@123!', $user);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('reuse', $errors[0]);
    }

    public function test_record_password_change_saves_history(): void
    {
        $user   = User::factory()->create();
        $hashed = Hash::make('NewPassword@123!');

        $this->service->recordPasswordChange($user, $hashed);

        $this->assertDatabaseHas('password_histories', ['user_id' => $user->id]);
        $this->assertNotNull($user->fresh()->password_changed_at);
        $this->assertNotNull($user->fresh()->password_expires_at);
    }
}
