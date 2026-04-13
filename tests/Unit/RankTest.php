<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_higher_rank_than_returns_true_when_rank_is_higher(): void
    {
        $user = new User(['rank' => 5]);
        $this->assertTrue($user->hasHigherRankThan(4));
    }

    public function test_has_higher_rank_than_returns_false_when_rank_is_equal(): void
    {
        $user = new User(['rank' => 5]);
        $this->assertFalse($user->hasHigherRankThan(5));
    }

    public function test_has_higher_rank_than_returns_false_when_rank_is_lower(): void
    {
        $user = new User(['rank' => 3]);
        $this->assertFalse($user->hasHigherRankThan(5));
    }

    public function test_has_minimum_rank_returns_true_when_rank_meets_minimum(): void
    {
        $user = new User(['rank' => 8]);
        $this->assertTrue($user->hasMinimumRank(8));
        $this->assertTrue($user->hasMinimumRank(5));
    }

    public function test_has_minimum_rank_returns_false_when_rank_below_minimum(): void
    {
        $user = new User(['rank' => 3]);
        $this->assertFalse($user->hasMinimumRank(5));
    }

    public function test_is_locked_returns_true_when_locked_until_is_future(): void
    {
        $user = new User(['locked_until' => now()->addMinutes(10)]);
        $this->assertTrue($user->isLocked());
    }

    public function test_is_locked_returns_false_when_locked_until_is_past(): void
    {
        $user = new User(['locked_until' => now()->subMinutes(10)]);
        $this->assertFalse($user->isLocked());
    }

    public function test_is_locked_returns_false_when_no_lock(): void
    {
        $user = new User(['locked_until' => null]);
        $this->assertFalse($user->isLocked());
    }

    public function test_password_expired_returns_true_when_past(): void
    {
        $user = new User(['password_expires_at' => now()->subDay()]);
        $this->assertTrue($user->isPasswordExpired());
    }

    public function test_password_expired_returns_false_when_future(): void
    {
        $user = new User(['password_expires_at' => now()->addDays(30)]);
        $this->assertFalse($user->isPasswordExpired());
    }
}
