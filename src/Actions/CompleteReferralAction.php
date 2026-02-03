<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Actions;

use LaravelPlus\Referral\Contracts\ReferralRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralServiceInterface;
use LaravelPlus\Referral\Enums\RewardAction;
use LaravelPlus\Referral\Models\Referral;

final class CompleteReferralAction
{
    public function __construct(
        private readonly ReferralServiceInterface $referralService,
        private readonly ReferralRepositoryInterface $repository,
    ) {}

    /**
     * Complete a referral for a user based on the configured trigger action.
     */
    public function execute(int $userId, string $action): ?Referral
    {
        $configuredTrigger = config('referral.rewards.trigger', 'email_verification');

        // Only process if the action matches the configured trigger
        $triggerAction = RewardAction::tryFrom($configuredTrigger);

        if (!$triggerAction || $triggerAction->value !== $action) {
            return null;
        }

        return $this->referralService->completeReferral($userId);
    }
}
