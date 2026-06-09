<?php

namespace Tests\Unit;

use App\Models\Application;
use PHPUnit\Framework\TestCase;

class ApplicationModelTest extends TestCase
{
    public function test_canBeEdited_returns_true_for_draft_status(): void
    {
        $app = new Application(['status' => Application::STATUS_DRAFT]);
        $this->assertTrue($app->canBeEdited());
    }

    public function test_canBeEdited_returns_true_for_needs_supplement_status(): void
    {
        $app = new Application(['status' => Application::STATUS_NEEDS_SUPPLEMENT]);
        $this->assertTrue($app->canBeEdited());
    }

    public function test_canBeEdited_returns_false_for_submitted_status(): void
    {
        $app = new Application(['status' => Application::STATUS_SUBMITTED]);
        $this->assertFalse($app->canBeEdited());
    }

    public function test_canBeEdited_returns_false_for_approved_status(): void
    {
        $app = new Application(['status' => Application::STATUS_APPROVED]);
        $this->assertFalse($app->canBeEdited());
    }

    public function test_canBeSubmitted_returns_true_for_draft(): void
    {
        $app = new Application(['status' => Application::STATUS_DRAFT]);
        $this->assertTrue($app->canBeSubmitted());
    }

    public function test_canBeSubmitted_returns_false_for_submitted(): void
    {
        $app = new Application(['status' => Application::STATUS_SUBMITTED]);
        $this->assertFalse($app->canBeSubmitted());
    }

    public function test_canBeSubmitted_returns_false_for_needs_supplement(): void
    {
        $app = new Application(['status' => Application::STATUS_NEEDS_SUPPLEMENT]);
        $this->assertFalse($app->canBeSubmitted());
    }

    public function test_isApproved_returns_true_for_approved_status(): void
    {
        $app = new Application(['status' => Application::STATUS_APPROVED]);
        $this->assertTrue($app->isApproved());
    }

    public function test_isApproved_returns_false_for_other_statuses(): void
    {
        foreach ([
            Application::STATUS_DRAFT,
            Application::STATUS_SUBMITTED,
            Application::STATUS_REJECTED,
            Application::STATUS_ACTIVE,
        ] as $status) {
            $app = new Application(['status' => $status]);
            $this->assertFalse($app->isApproved(), "Expected isApproved() = false for status: $status");
        }
    }

    public function test_isRejected_returns_true_for_rejected_status(): void
    {
        $app = new Application(['status' => Application::STATUS_REJECTED]);
        $this->assertTrue($app->isRejected());
    }

    public function test_isProgramA_returns_true_when_call_id_set(): void
    {
        $app = new Application(['call_id' => 1, 'challenge_id' => null]);
        $this->assertTrue($app->isProgramA());
    }

    public function test_isProgramA_returns_false_when_call_id_null(): void
    {
        $app = new Application(['call_id' => null]);
        $this->assertFalse($app->isProgramA());
    }

    public function test_isProgramB_returns_true_when_challenge_id_set(): void
    {
        $app = new Application(['call_id' => null, 'challenge_id' => 5]);
        $this->assertTrue($app->isProgramB());
    }

    public function test_isProgramB_returns_false_when_challenge_id_null(): void
    {
        $app = new Application(['challenge_id' => null]);
        $this->assertFalse($app->isProgramB());
    }

    public function test_status_constants_are_defined(): void
    {
        $statuses = [
            Application::STATUS_DRAFT,
            Application::STATUS_SUBMITTED,
            Application::STATUS_VERIFIED,
            Application::STATUS_IN_EVALUATION,
            Application::STATUS_NEEDS_SUPPLEMENT,
            Application::STATUS_APPROVED,
            Application::STATUS_REJECTED,
            Application::STATUS_ONBOARDING,
            Application::STATUS_ACTIVE,
            Application::STATUS_SUSPENDED,
            Application::STATUS_ARCHIVED,
        ];

        foreach ($statuses as $status) {
            $this->assertIsString($status);
            $this->assertNotEmpty($status);
        }

        $this->assertCount(11, array_unique($statuses), 'All status constants must be unique');
    }
}
