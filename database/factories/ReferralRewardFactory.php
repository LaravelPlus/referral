<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPlus\Referral\Enums\RewardType;
use LaravelPlus\Referral\Models\Referral;
use LaravelPlus\Referral\Models\ReferralReward;

/**
 * @extends Factory<ReferralReward>
 */
final class ReferralRewardFactory extends Factory
{
    protected $model = ReferralReward::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referral_id' => Referral::factory(),
            'user_id' => User::factory(),
            'type' => RewardType::Referrer,
            'reward_type' => 'points',
            'reward_value' => 100,
            'granted_at' => now(),
            'metadata' => null,
        ];
    }

    public function forReferee(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => RewardType::Referee,
            'reward_value' => 50,
        ]);
    }

    public function forReferrer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => RewardType::Referrer,
            'reward_value' => 100,
        ]);
    }
}
