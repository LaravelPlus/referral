<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Referral\Contracts\ReferralRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralServiceInterface;
use LaravelPlus\Referral\Enums\ReferralChannel;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Events\ReferralCompleted;
use LaravelPlus\Referral\Events\ReferralCreated;
use LaravelPlus\Referral\Events\ReferralRewarded;
use LaravelPlus\Referral\Models\Referral;

final class ReferralService implements ReferralServiceInterface
{
    public function __construct(
        private readonly ReferralRepositoryInterface $repository,
        private readonly ReferralCodeService $codeService,
        private readonly ReferralRewardService $rewardService,
    ) {}

    /**
     * Create a new referral for a user.
     */
    public function createForUser(int $userId, ?string $email = null, string $channel = 'link'): Referral
    {
        $referral = $this->repository->create([
            'referrer_id' => $userId,
            'code' => $this->codeService->generate(),
            'email' => $email,
            'channel' => ReferralChannel::from($channel),
            'status' => ReferralStatus::Pending,
            'expires_at' => config('referral.expiration.enabled', false)
                ? now()->addDays((int) config('referral.expiration.days', 30))
                : null,
        ]);

        ReferralCreated::dispatch($referral);

        return $referral;
    }

    /**
     * Track a referral code visit. Stores the referral in the session.
     */
    public function track(string $code): ?Referral
    {
        return $this->codeService->resolve($code);
    }

    /**
     * Complete a referral when the referee performs the trigger action.
     */
    public function completeReferral(int $refereeId): ?Referral
    {
        return DB::transaction(function () use ($refereeId): ?Referral {
            $referral = $this->repository->findPendingForReferee($refereeId);

            if (!$referral) {
                return null;
            }

            $referral = $this->repository->update($referral, [
                'status' => ReferralStatus::Completed,
                'completed_at' => now(),
            ]);

            ReferralCompleted::dispatch($referral);

            $this->processReward($referral);

            return $referral;
        });
    }

    /**
     * Process reward for a completed referral.
     */
    public function processReward(Referral $referral): void
    {
        if (!$this->rewardService->canReward($referral)) {
            return;
        }

        DB::transaction(function () use ($referral): void {
            $this->rewardService->grant($referral);

            $this->repository->update($referral, [
                'status' => ReferralStatus::Rewarded,
                'rewarded_at' => now(),
            ]);

            ReferralRewarded::dispatch($referral->fresh());
        });
    }

    /**
     * Get referral statistics for a user.
     *
     * @return array{total: int, pending: int, completed: int, rewarded: int, total_rewards: int}
     */
    public function getUserStats(int $userId): array
    {
        $referrals = $this->repository->forUser($userId);

        return [
            'total' => $referrals->count(),
            'pending' => $referrals->where('status', ReferralStatus::Pending)->count(),
            'completed' => $referrals->where('status', ReferralStatus::Completed)->count(),
            'rewarded' => $referrals->where('status', ReferralStatus::Rewarded)->count(),
            'total_rewards' => $this->rewardService->getTotalRewards($userId),
        ];
    }

    /**
     * List referrals with optional search and status filter.
     */
    public function list(int $perPage = 15, ?string $search = null, ?ReferralStatus $status = null): LengthAwarePaginator
    {
        return $this->repository->list($perPage, $search, $status);
    }
}
