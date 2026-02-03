<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use LaravelPlus\Referral\Contracts\ReferralServiceInterface;
use LaravelPlus\Referral\Enums\ReferralChannel;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Events\ReferralCompleted;
use LaravelPlus\Referral\Events\ReferralCreated;
use LaravelPlus\Referral\Events\ReferralRewarded;
use LaravelPlus\Referral\Models\Referral;
use LaravelPlus\Referral\Services\ReferralService;
use Tests\TestCase;

final class ReferralServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferralService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReferralServiceInterface::class);
    }

    public function test_create_for_user_creates_referral_with_code(): void
    {
        Event::fake();

        $user = User::factory()->create();

        $referral = $this->service->createForUser($user->id);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $user->id,
            'channel' => ReferralChannel::Link->value,
            'status' => ReferralStatus::Pending->value,
        ]);

        $this->assertNotEmpty($referral->code);
        Event::assertDispatched(ReferralCreated::class);
    }

    public function test_create_for_user_with_email_channel(): void
    {
        Event::fake();

        $user = User::factory()->create();

        $referral = $this->service->createForUser($user->id, 'test@example.com', 'email');

        $this->assertSame('test@example.com', $referral->email);
        $this->assertSame(ReferralChannel::Email, $referral->channel);
    }

    public function test_create_for_user_with_expiration(): void
    {
        config(['referral.expiration.enabled' => true]);
        config(['referral.expiration.days' => 14]);

        Event::fake();

        $user = User::factory()->create();
        $referral = $this->service->createForUser($user->id);

        $this->assertNotNull($referral->expires_at);
    }

    public function test_track_returns_referral_for_valid_code(): void
    {
        $referral = Referral::factory()->create();

        $tracked = $this->service->track($referral->code);

        $this->assertNotNull($tracked);
        $this->assertSame($referral->id, $tracked->id);
    }

    public function test_track_returns_null_for_invalid_code(): void
    {
        $this->assertNull($this->service->track('INVALID'));
    }

    public function test_complete_referral_completes_and_rewards(): void
    {
        Event::fake();

        $referrer = User::factory()->create();
        $referee = User::factory()->create();

        $referral = Referral::factory()
            ->withReferrer($referrer->id)
            ->withReferee($referee->id)
            ->create();

        $completed = $this->service->completeReferral($referee->id);

        $this->assertNotNull($completed);
        $this->assertSame(ReferralStatus::Rewarded, $completed->fresh()->status);

        Event::assertDispatched(ReferralCompleted::class);
        Event::assertDispatched(ReferralRewarded::class);
    }

    public function test_complete_referral_returns_null_when_no_pending(): void
    {
        $user = User::factory()->create();

        $this->assertNull($this->service->completeReferral($user->id));
    }

    public function test_get_user_stats_returns_correct_counts(): void
    {
        $user = User::factory()->create();

        Referral::factory()->withReferrer($user->id)->count(3)->create();
        Referral::factory()->withReferrer($user->id)->completed()->count(2)->create();
        Referral::factory()->withReferrer($user->id)->rewarded()->create();

        $stats = $this->service->getUserStats($user->id);

        $this->assertSame(6, $stats['total']);
        $this->assertSame(3, $stats['pending']);
        $this->assertSame(2, $stats['completed']);
        $this->assertSame(1, $stats['rewarded']);
    }

    public function test_list_returns_paginated_results(): void
    {
        Referral::factory()->count(20)->create();

        $result = $this->service->list(perPage: 10);

        $this->assertSame(10, $result->perPage());
        $this->assertSame(20, $result->total());
    }

    public function test_list_filters_by_status(): void
    {
        Referral::factory()->count(3)->create();
        Referral::factory()->completed()->count(2)->create();

        $result = $this->service->list(status: ReferralStatus::Completed);

        $this->assertSame(2, $result->total());
    }
}
