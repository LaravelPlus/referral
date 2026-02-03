<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Console\Commands;

use Illuminate\Console\Command;
use LaravelPlus\Referral\Contracts\ReferralRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralRewardRepositoryInterface;
use LaravelPlus\Referral\Enums\ReferralStatus;

final class StatsCommand extends Command
{
    protected $signature = 'referral:stats';

    protected $description = 'Display referral system statistics.';

    public function handle(
        ReferralRepositoryInterface $referralRepository,
        ReferralRewardRepositoryInterface $rewardRepository,
    ): int {
        $this->components->info('Referral System Statistics');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Pending Referrals', $referralRepository->countByStatus(ReferralStatus::Pending)],
                ['Completed Referrals', $referralRepository->countByStatus(ReferralStatus::Completed)],
                ['Rewarded Referrals', $referralRepository->countByStatus(ReferralStatus::Rewarded)],
                ['Expired Referrals', $referralRepository->countByStatus(ReferralStatus::Expired)],
                ['Total Rewards Granted', $rewardRepository->count()],
            ],
        );

        return self::SUCCESS;
    }
}
