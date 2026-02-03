<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Repositories;

use LaravelPlus\Referral\Contracts\ReferralInvitationRepositoryInterface;
use LaravelPlus\Referral\Models\ReferralInvitation;

final class ReferralInvitationRepository implements ReferralInvitationRepositoryInterface
{
    public private(set) string $modelClass = ReferralInvitation::class;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ReferralInvitation
    {
        return $this->modelClass::query()->create($data);
    }

    public function findByToken(string $token): ?ReferralInvitation
    {
        return $this->modelClass::query()
            ->where('token', $token)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ReferralInvitation $invitation, array $data): ReferralInvitation
    {
        $invitation->update($data);

        return $invitation->fresh();
    }

    public function countSentTodayByUser(int $userId): int
    {
        return $this->modelClass::query()
            ->whereHas('referral', fn ($q) => $q->where('referrer_id', $userId))
            ->whereDate('sent_at', today())
            ->count();
    }
}
