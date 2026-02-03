<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Contracts;

use LaravelPlus\Referral\Models\ReferralInvitation;

interface ReferralInvitationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ReferralInvitation;

    public function findByToken(string $token): ?ReferralInvitation;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ReferralInvitation $invitation, array $data): ReferralInvitation;

    public function countSentTodayByUser(int $userId): int;
}
