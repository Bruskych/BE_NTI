<?php

namespace Tests\Unit;

use App\Services\EmailConfirmationService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EmailConfirmationServiceTest extends TestCase
{
    private EmailConfirmationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmailConfirmationService();
        Cache::flush();
    }

    public function test_generateCode_returns_6_character_string(): void
    {
        $code = $this->service->generateCode('user@example.com');
        $this->assertIsString($code);
        $this->assertSame(6, strlen($code));
    }

    public function test_generateCode_stores_code_in_cache(): void
    {
        $this->service->generateCode('user@example.com');
        $this->assertTrue($this->service->exists('user@example.com'));
    }

    public function test_generateCode_stores_associated_data(): void
    {
        $data = ['document_id' => 42];
        $code = $this->service->generateCode('user@example.com', $data);

        $result = $this->service->verify('user@example.com', $code);
        $this->assertSame(['document_id' => 42], $result);
    }

    public function test_verify_returns_data_for_correct_code(): void
    {
        $code = $this->service->generateCode('user@example.com', ['foo' => 'bar']);
        $result = $this->service->verify('user@example.com', $code);

        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function test_verify_returns_null_for_wrong_code(): void
    {
        $this->service->generateCode('user@example.com');
        $result = $this->service->verify('user@example.com', 'WRONG1');

        $this->assertNull($result);
    }

    public function test_verify_removes_code_from_cache_after_success(): void
    {
        $code = $this->service->generateCode('user@example.com');
        $this->service->verify('user@example.com', $code);

        $this->assertFalse($this->service->exists('user@example.com'));
    }

    public function test_verify_does_not_remove_code_after_wrong_attempt(): void
    {
        $this->service->generateCode('user@example.com');
        $this->service->verify('user@example.com', 'WRONG1');

        $this->assertTrue($this->service->exists('user@example.com'));
    }

    public function test_purpose_isolates_codes_from_different_purposes(): void
    {
        $codeA = $this->service->generateCode('user@example.com', [], 'email_verification');
        $codeB = $this->service->generateCode('user@example.com', [], 'document_access');

        // Verifying code A with purpose B should fail
        $resultAonB = $this->service->verify('user@example.com', $codeA, 'document_access');
        $this->assertNull($resultAonB);

        // Code B should still be valid
        $resultB = $this->service->verify('user@example.com', $codeB, 'document_access');
        $this->assertSame([], $resultB);
    }

    public function test_purpose_code_does_not_affect_no_purpose_cache(): void
    {
        $codeWithPurpose = $this->service->generateCode('user@example.com', [], 'purpose_x');
        $codeNoPurpose   = $this->service->generateCode('user@example.com', []);

        $this->assertFalse($this->service->exists('user@example.com', 'other_purpose'));
        $this->assertTrue($this->service->exists('user@example.com'));
        $this->assertTrue($this->service->exists('user@example.com', 'purpose_x'));
    }

    public function test_exists_returns_false_when_no_code_generated(): void
    {
        $this->assertFalse($this->service->exists('nobody@example.com'));
    }

    public function test_revoke_removes_code_from_cache(): void
    {
        $this->service->generateCode('user@example.com');
        $this->service->revoke('user@example.com');

        $this->assertFalse($this->service->exists('user@example.com'));
    }

    public function test_second_generateCode_overwrites_first_for_same_email_and_purpose(): void
    {
        $codeFirst  = $this->service->generateCode('user@example.com');
        $codeSecond = $this->service->generateCode('user@example.com');

        // First code is no longer valid
        $resultFirst = $this->service->verify('user@example.com', $codeFirst);
        $this->assertNull($resultFirst);

        // Re-generate since verify succeeded and cleared the cache
        $codeSecond = $this->service->generateCode('user@example.com');
        $resultSecond = $this->service->verify('user@example.com', $codeSecond);
        $this->assertSame([], $resultSecond);
    }

    public function test_codes_for_different_emails_are_independent(): void
    {
        $codeAlice = $this->service->generateCode('alice@example.com');
        $codeBob   = $this->service->generateCode('bob@example.com');

        // Alice's code doesn't work for Bob
        $this->assertNull($this->service->verify('bob@example.com', $codeAlice));

        // Bob's code still valid
        $this->assertSame([], $this->service->verify('bob@example.com', $codeBob));
    }
}
