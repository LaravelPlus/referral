<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Referral\Models\Referral;
use Tests\TestCase;

final class ReferralControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_link_redirects_to_register(): void
    {
        $referral = Referral::factory()->create();

        $response = $this->get("/ref/{$referral->code}");

        $response->assertRedirect('/register');
    }

    public function test_referral_link_stores_code_in_session(): void
    {
        $referral = Referral::factory()->create();

        $this->get("/ref/{$referral->code}");

        $this->assertSame($referral->code, session('referral_code'));
    }

    public function test_invalid_referral_code_still_redirects(): void
    {
        $response = $this->get('/ref/INVALIDCODE');

        $response->assertRedirect('/register');
    }

    public function test_invalid_code_does_not_store_session(): void
    {
        $this->get('/ref/INVALIDCODE');

        $this->assertNull(session('referral_code'));
    }

    public function test_custom_redirect_to_config(): void
    {
        config(['referral.link.redirect_to' => '/signup']);

        $referral = Referral::factory()->create();

        $response = $this->get("/ref/{$referral->code}");

        $response->assertRedirect('/signup');
    }
}
