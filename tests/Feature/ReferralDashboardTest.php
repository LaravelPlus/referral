<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_referral_dashboard(): void
    {
        $response = $this->get('/referral/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/referral/dashboard');

        $response->assertOk();
    }
}
