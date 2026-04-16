<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Rank 5 Legal Consultant / Auditor access logic.
 *
 * Core rule: rank >= 5 → global read-only; BLOCKED from all writes.
 */
class Rank5AuditorTest extends TestCase
{
    use RefreshDatabase;

    private User $auditor;       // rank 5
    private User $investigator;  // rank 3
    private User $lowRank;       // rank 2
    private User $admin;         // rank 8

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditor      = User::factory()->create(['rank' => 5]);
        $this->investigator = User::factory()->create(['rank' => 3]);
        $this->lowRank      = User::factory()->create(['rank' => 2]);
        $this->admin        = User::factory()->create(['rank' => 8]);
    }

    // ── isAuditor ─────────────────────────────────────────────────────────────

    public function test_rank_5_is_auditor(): void
    {
        $this->assertTrue($this->auditor->isAuditor());
    }

    public function test_rank_3_is_not_auditor(): void
    {
        $this->assertFalse($this->investigator->isAuditor());
    }

    public function test_rank_2_is_not_auditor(): void
    {
        $this->assertFalse($this->lowRank->isAuditor());
    }

    public function test_rank_8_is_auditor_too(): void
    {
        // Admin also passes isAuditor (rank >= 5)
        $this->assertTrue($this->admin->isAuditor());
    }

    // ── isReadOnlyAuditor ─────────────────────────────────────────────────────

    public function test_rank_5_is_read_only_auditor(): void
    {
        $this->assertTrue($this->auditor->isReadOnlyAuditor());
    }

    public function test_rank_7_is_read_only_auditor(): void
    {
        $user = User::factory()->create(['rank' => 7]);
        $this->assertTrue($user->isReadOnlyAuditor());
    }

    public function test_rank_8_is_not_read_only_auditor(): void
    {
        // Admin has full write — not read-only
        $this->assertFalse($this->admin->isReadOnlyAuditor());
    }

    public function test_rank_3_is_not_read_only_auditor(): void
    {
        $this->assertFalse($this->investigator->isReadOnlyAuditor());
    }

    // ── canViewEvidenceGlobally ───────────────────────────────────────────────

    public function test_rank_5_can_view_evidence_globally(): void
    {
        $this->assertTrue($this->auditor->canViewEvidenceGlobally());
    }

    public function test_rank_3_cannot_view_evidence_globally(): void
    {
        // Investigator is case-scoped, not global
        $this->assertFalse($this->investigator->canViewEvidenceGlobally());
    }

    public function test_rank_2_cannot_view_evidence_globally(): void
    {
        $this->assertFalse($this->lowRank->canViewEvidenceGlobally());
    }

    public function test_rank_8_can_view_evidence_globally(): void
    {
        $this->assertTrue($this->admin->canViewEvidenceGlobally());
    }

    // ── canRunSystemAudit ─────────────────────────────────────────────────────

    public function test_rank_5_can_run_system_audit(): void
    {
        $this->assertTrue($this->auditor->canRunSystemAudit());
    }

    public function test_rank_3_cannot_run_system_audit(): void
    {
        $this->assertFalse($this->investigator->canRunSystemAudit());
    }

    public function test_rank_2_cannot_run_system_audit(): void
    {
        $this->assertFalse($this->lowRank->canRunSystemAudit());
    }

    // ── EvidencePolicy via Gate ───────────────────────────────────────────────

    public function test_auditor_can_view_any_evidence(): void
    {
        $this->assertTrue(
            $this->auditor->can('viewAny', \App\Models\Evidence::class)
        );
    }

    public function test_low_rank_cannot_view_any_evidence(): void
    {
        $this->assertFalse(
            $this->lowRank->can('viewAny', \App\Models\Evidence::class)
        );
    }

    public function test_auditor_cannot_create_evidence(): void
    {
        $this->assertFalse(
            $this->auditor->can('create', \App\Models\Evidence::class)
        );
    }

    public function test_investigator_can_create_evidence(): void
    {
        $this->assertTrue(
            $this->investigator->can('create', \App\Models\Evidence::class)
        );
    }

    public function test_admin_can_create_evidence(): void
    {
        $this->assertTrue(
            $this->admin->can('create', \App\Models\Evidence::class)
        );
    }

    public function test_auditor_can_run_system_audit_via_policy(): void
    {
        $this->assertTrue(
            $this->auditor->can('runSystemAudit', \App\Models\Evidence::class)
        );
    }

    public function test_investigator_cannot_run_system_audit_via_policy(): void
    {
        $this->assertFalse(
            $this->investigator->can('runSystemAudit', \App\Models\Evidence::class)
        );
    }
}
