<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tests for Rank 3 Senior Investigator access logic.
 *
 * Core rule: rank >= 3 AND user is primary/secondary investigator on the case.
 */
class Rank3InvestigatorTest extends TestCase
{
    use RefreshDatabase;

    private User $investigator;
    private User $lowRankUser;
    private User $admin;
    private int  $assignedCaseId;
    private int  $unassignedCaseId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->investigator = User::factory()->create(['rank' => 3]);
        $this->lowRankUser  = User::factory()->create(['rank' => 2]);
        $this->admin        = User::factory()->create(['rank' => 8]);

        // Create a minimal cases table for testing
        DB::statement('CREATE TABLE IF NOT EXISTS cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            primary_investigator_id INTEGER,
            secondary_investigator_id INTEGER
        )');

        // Case where investigator is primary
        $this->assignedCaseId = DB::table('cases')->insertGetId([
            'title'                      => 'Test Case A',
            'primary_investigator_id'    => $this->investigator->id,
            'secondary_investigator_id'  => null,
        ]);

        // Case where investigator is NOT assigned
        $this->unassignedCaseId = DB::table('cases')->insertGetId([
            'title'                      => 'Test Case B',
            'primary_investigator_id'    => 9999,
            'secondary_investigator_id'  => null,
        ]);
    }

    // ─── hasMinimumRank ───────────────────────────────────────────────────

    public function test_rank_3_meets_minimum_rank_3(): void
    {
        $this->assertTrue($this->investigator->hasMinimumRank(3));
    }

    public function test_rank_2_does_not_meet_minimum_rank_3(): void
    {
        $this->assertFalse($this->lowRankUser->hasMinimumRank(3));
    }

    // ─── isSeniorInvestigatorOnCase ───────────────────────────────────────

    public function test_rank_3_assigned_as_primary_passes(): void
    {
        $this->assertTrue(
            $this->investigator->isSeniorInvestigatorOnCase($this->assignedCaseId)
        );
    }

    public function test_rank_3_not_assigned_fails(): void
    {
        $this->assertFalse(
            $this->investigator->isSeniorInvestigatorOnCase($this->unassignedCaseId)
        );
    }

    public function test_rank_2_fails_even_if_assigned(): void
    {
        // Give low-rank user primary assignment — rank check should still fail
        DB::table('cases')->where('id', $this->assignedCaseId)->update([
            'primary_investigator_id' => $this->lowRankUser->id,
        ]);

        $this->assertFalse(
            $this->lowRankUser->isSeniorInvestigatorOnCase($this->assignedCaseId)
        );
    }

    public function test_secondary_investigator_also_passes(): void
    {
        $secondary = User::factory()->create(['rank' => 3]);

        DB::table('cases')->where('id', $this->assignedCaseId)->update([
            'secondary_investigator_id' => $secondary->id,
        ]);

        $this->assertTrue(
            $secondary->isSeniorInvestigatorOnCase($this->assignedCaseId)
        );
    }

    // ─── canManageEvidenceOnCase ──────────────────────────────────────────

    public function test_admin_rank_8_bypasses_case_assignment(): void
    {
        // Admin is NOT assigned to the case but should still pass
        $this->assertTrue(
            $this->admin->canManageEvidenceOnCase($this->unassignedCaseId)
        );
    }

    public function test_rank_3_assigned_can_manage_evidence(): void
    {
        $this->assertTrue(
            $this->investigator->canManageEvidenceOnCase($this->assignedCaseId)
        );
    }

    public function test_rank_3_unassigned_cannot_manage_evidence(): void
    {
        $this->assertFalse(
            $this->investigator->canManageEvidenceOnCase($this->unassignedCaseId)
        );
    }

    // ─── canTransferCustody & canVerifyEvidenceIntegrity ─────────────────

    public function test_rank_3_assigned_can_transfer_custody(): void
    {
        $this->assertTrue(
            $this->investigator->canTransferCustody($this->assignedCaseId)
        );
    }

    public function test_rank_3_unassigned_cannot_transfer_custody(): void
    {
        $this->assertFalse(
            $this->investigator->canTransferCustody($this->unassignedCaseId)
        );
    }

    public function test_rank_3_assigned_can_verify_integrity(): void
    {
        $this->assertTrue(
            $this->investigator->canVerifyEvidenceIntegrity($this->assignedCaseId)
        );
    }
}
