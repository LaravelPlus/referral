<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Services;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Referral\Contracts\ReferralRewardRepositoryInterface;
use LaravelPlus\Referral\Enums\RewardType;
use LaravelPlus\Referral\Models\Referral;
use LaravelPlus\Referral\Models\ReferralReward;

final class ReferralRewardService
{
    public function __construct(
        private readonly ReferralRewardRepositoryInterface $repository,
    ) {}

    /**
     * Grant rewards for a completed referral.
     */
    public function grant(Referral $referral): void
    {
        if (!config('referral.rewards.enabled', true)) {
            return;
        }

        // Grant referrer reward
        $this->repository->create([
            'referral_id' => $referral->id,
            'user_id' => $referral->referrer_id,
            'type' => RewardType::Referrer,
            'reward_type' => config('referral.rewards.referrer_type', 'points'),
            'reward_value' => config('referral.rewards.referrer_value', 100),
            'granted_at' => now(),
        ]);

        // Grant referee reward
        if ($referral->referee_id) {
            $this->repository->create([
                'referral_id' => $referral->id,
                'user_id' => $referral->referee_id,
                'type' => RewardType::Referee,
                'reward_type' => config('referral.rewards.referee_type', 'points'),
                'reward_value' => config('referral.rewards.referee_value', 50),
                'granted_at' => now(),
            ]);
        }
    }

    /**
     * Check if a referral can receive rewards.
     */
    public function canReward(Referral $referral): bool
    {
        if (!config('referral.rewards.enabled', true)) {
            return false;
        }

        // Already rewarded
        if ($referral->rewarded_at !== null) {
            return false;
        }

        // Check max referrals limit
        $maxReferrals = (int) config('referral.rewards.max_referrals', 0);

        if ($maxReferrals > 0) {
            $count = Referral::query()
                ->where('referrer_id', $referral->referrer_id)
                ->whereNotNull('rewarded_at')
                ->count();

            if ($count >= $maxReferrals) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all rewards for a user.
     *
     * @return Collection<int, ReferralReward>
     */
    public function getRewardsForUser(int $userId): Collection
    {
        return $this->repository->forUser($userId);
    }

    /**
     * Get total reward value for a user.
     */
    public function getTotalRewards(int $userId): int
    {
        return $this->repository->totalForUser($userId);
    }
}
