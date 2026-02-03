<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Contracts;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Referral\Models\ReferralReward;

interface ReferralRewardRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ReferralReward;

    /**
     * @return Collection<int, ReferralReward>
     */
    public function forUser(int $userId): Collection;

    /**
     * @return Collection<int, ReferralReward>
     */
    public function forReferral(int $referralId): Collection;

    public function totalForUser(int $userId): int;

    public function count(): int;
}
