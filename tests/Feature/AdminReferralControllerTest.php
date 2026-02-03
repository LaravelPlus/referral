<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Tests\Feature;

use App\Constants\RoleNames;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Referral\Models\Referral;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class AdminReferralControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleNames::SUPER_ADMIN]);
        Role::create(['name' => RoleNames::ADMIN]);
        Role::create(['name' => RoleNames::USER]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(RoleNames::ADMIN);

        $this->user = User::factory()->create();
        $this->user->assignRole(RoleNames::USER);
    }

    public function test_guest_cannot_access_admin_referrals(): void
    {
        $response = $this->get('/admin/referrals');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_referrals(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/referrals');

        $response->assertForbidden();
    }

    public function test_admin_can_access_referrals_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/referrals');

        $response->assertOk();
    }

    public function test_admin_can_view_referral_detail(): void
    {
        $referral = Referral::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/referrals/{$referral->id}");

        $response->assertOk();
    }

    public function test_admin_index_shows_referrals(): void
    {
        Referral::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get('/admin/referrals');

        $response->assertOk();
    }

    public function test_admin_can_filter_by_status(): void
    {
        Referral::factory()->count(3)->create();
        Referral::factory()->completed()->count(2)->create();

        $response = $this->actingAs($this->admin)->get('/admin/referrals?status=completed');

        $response->assertOk();
    }

    public function test_admin_can_search_referrals(): void
    {
        Referral::factory()->create(['code' => 'TESTCODE']);

        $response = $this->actingAs($this->admin)->get('/admin/referrals?search=TESTCODE');

        $response->assertOk();
    }
}
