<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Referral\Enums\RewardType;
use LaravelPlus\Referral\Models\Referral;
use LaravelPlus\Referral\Models\ReferralReward;
use LaravelPlus\Referral\Services\ReferralRewardService;
use Tests\TestCase;

final class ReferralRewardServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferralRewardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReferralRewardService::class);
    }

    public function test_grant_creates_referrer_and_referee_rewards(): void
    {
        $referrer = User::factory()->create();
        $referee = User::factory()->create();

        $referral = Referral::factory()
            ->completed()
            ->withReferrer($referrer->id)
            ->withReferee($referee->id)
            ->create();

        $this->service->grant($referral);

        $this->assertDatabaseHas('referral_rewards', [
            'referral_id' => $referral->id,
            'user_id' => $referrer->id,
            'type' => RewardType::Referrer->value,
        ]);

        $this->assertDatabaseHas('referral_rewards', [
            'referral_id' => $referral->id,
            'user_id' => $referee->id,
            'type' => RewardType::Referee->value,
        ]);
    }

    public function test_grant_does_nothing_when_rewards_disabled(): void
    {
        config(['referral.rewards.enabled' => false]);

        $referral = Referral::factory()->completed()->create();

        $this->service->grant($referral);

        $this->assertDatabaseMissing('referral_rewards', [
            'referral_id' => $referral->id,
        ]);
    }

    public function test_grant_uses_configured_reward_values(): void
    {
        config(['referral.rewards.referrer_value' => 200]);
        config(['referral.rewards.referee_value' => 75]);

        $referrer = User::factory()->create();
        $referee = User::factory()->create();

        $referral = Referral::factory()
            ->completed()
            ->withReferrer($referrer->id)
            ->withReferee($referee->id)
            ->create();

        $this->service->grant($referral);

        $this->assertDatabaseHas('referral_rewards', [
            'user_id' => $referrer->id,
            'reward_value' => 200,
        ]);

        $this->assertDatabaseHas('referral_rewards', [
            'user_id' => $referee->id,
            'reward_value' => 75,
        ]);
    }

    public function test_can_reward_returns_false_when_already_rewarded(): void
    {
        $referral = Referral::factory()->rewarded()->create();

        $this->assertFalse($this->service->canReward($referral));
    }

    public function test_can_reward_returns_false_when_rewards_disabled(): void
    {
        config(['referral.rewards.enabled' => false]);

        $referral = Referral::factory()->completed()->create();

        $this->assertFalse($this->service->canReward($referral));
    }

    public function test_can_reward_respects_max_referrals_limit(): void
    {
        config(['referral.rewards.max_referrals' => 1]);

        $referrer = User::factory()->create();

        // Create one already-rewarded referral
        Referral::factory()
            ->withReferrer($referrer->id)
            ->rewarded()
            ->create();

        // Create another pending referral
        $referral = Referral::factory()
            ->withReferrer($referrer->id)
            ->completed()
            ->create();

        $this->assertFalse($this->service->canReward($referral));
    }

    public function test_can_reward_allows_when_under_limit(): void
    {
        config(['referral.rewards.max_referrals' => 5]);

        $referrer = User::factory()->create();

        $referral = Referral::factory()
            ->withReferrer($referrer->id)
            ->completed()
            ->create();

        $this->assertTrue($this->service->canReward($referral));
    }

    public function test_get_total_rewards_returns_sum(): void
    {
        $user = User::factory()->create();

        ReferralReward::factory()->create([
            'user_id' => $user->id,
            'reward_value' => 100,
        ]);

        ReferralReward::factory()->create([
            'user_id' => $user->id,
            'reward_value' => 50,
        ]);

        $this->assertSame(150, $this->service->getTotalRewards($user->id));
    }

    public function test_get_rewards_for_user_returns_collection(): void
    {
        $user = User::factory()->create();

        ReferralReward::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $rewards = $this->service->getRewardsForUser($user->id);

        $this->assertCount(3, $rewards);
    }
}
