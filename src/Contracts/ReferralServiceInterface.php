<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Models\Referral;

interface ReferralServiceInterface
{
    public function createForUser(int $userId, ?string $email = null, string $channel = 'link'): Referral;

    public function track(string $code): ?Referral;

    public function completeReferral(int $refereeId): ?Referral;

    public function processReward(Referral $referral): void;

    /**
     * @return array{total: int, pending: int, completed: int, rewarded: int, total_rewards: int}
     */
    public function getUserStats(int $userId): array;

    public function list(int $perPage = 15, ?string $search = null, ?ReferralStatus $status = null): LengthAwarePaginator;
}
