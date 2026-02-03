<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Referral\Models\Referral;
use LaravelPlus\Referral\Services\ReferralCodeService;
use Tests\TestCase;

final class ReferralCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferralCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReferralCodeService();
    }

    public function test_generate_returns_code_with_configured_length(): void
    {
        config(['referral.code.length' => 8]);
        config(['referral.code.prefix' => '']);

        $code = $this->service->generate();

        $this->assertSame(8, mb_strlen($code));
    }

    public function test_generate_returns_code_with_prefix(): void
    {
        config(['referral.code.prefix' => 'REF-']);
        config(['referral.code.length' => 6]);

        $code = $this->service->generate();

        $this->assertStringStartsWith('REF-', $code);
        $this->assertSame(10, mb_strlen($code));
    }

    public function test_generate_returns_unique_codes(): void
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = $this->service->generate();
        }

        $this->assertCount(10, array_unique($codes));
    }

    public function test_validate_returns_true_for_existing_code(): void
    {
        $referral = Referral::factory()->create();

        $this->assertTrue($this->service->validate($referral->code));
    }

    public function test_validate_returns_false_for_nonexistent_code(): void
    {
        $this->assertFalse($this->service->validate('NONEXISTENT'));
    }

    public function test_resolve_returns_referral_for_valid_code(): void
    {
        $referral = Referral::factory()->create();

        $resolved = $this->service->resolve($referral->code);

        $this->assertNotNull($resolved);
        $this->assertSame($referral->id, $resolved->id);
    }

    public function test_resolve_returns_null_for_invalid_code(): void
    {
        $this->assertNull($this->service->resolve('INVALID'));
    }

    public function test_is_available_returns_true_for_unused_code(): void
    {
        $this->assertTrue($this->service->isAvailable('NEWCODE1'));
    }

    public function test_is_available_returns_false_for_used_code(): void
    {
        $referral = Referral::factory()->create();

        $this->assertFalse($this->service->isAvailable($referral->code));
    }

    public function test_generate_uses_only_allowed_characters(): void
    {
        config(['referral.code.characters' => 'ABC']);
        config(['referral.code.prefix' => '']);
        config(['referral.code.length' => 20]);

        $code = $this->service->generate();

        $this->assertMatchesRegularExpression('/^[ABC]+$/', $code);
    }
}
