<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Repositories;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Referral\Contracts\ReferralRewardRepositoryInterface;
use LaravelPlus\Referral\Models\ReferralReward;

final class ReferralRewardRepository implements ReferralRewardRepositoryInterface
{
    public private(set) string $modelClass = ReferralReward::class;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ReferralReward
    {
        return $this->modelClass::query()->create($data);
    }

    /**
     * @return Collection<int, ReferralReward>
     */
    public function forUser(int $userId): Collection
    {
        return $this->modelClass::query()
            ->where('user_id', $userId)
            ->latest('granted_at')
            ->get();
    }

    /**
     * @return Collection<int, ReferralReward>
     */
    public function forReferral(int $referralId): Collection
    {
        return $this->modelClass::query()
            ->where('referral_id', $referralId)
            ->get();
    }

    public function totalForUser(int $userId): int
    {
        return (int) $this->modelClass::query()
            ->where('user_id', $userId)
            ->sum('reward_value');
    }

    public function count(): int
    {
        return $this->modelClass::query()->count();
    }
}
