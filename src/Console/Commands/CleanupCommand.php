<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Console\Commands;

use Illuminate\Console\Command;
use LaravelPlus\Referral\Contracts\ReferralRepositoryInterface;
use LaravelPlus\Referral\Enums\ReferralStatus;

final class CleanupCommand extends Command
{
    protected $signature = 'referral:cleanup
        {--days= : Number of days after which pending referrals expire}';

    protected $description = 'Expire old pending referrals.';

    public function handle(ReferralRepositoryInterface $repository): int
    {
        $days = (int) ($this->option('days') ?? config('referral.expiration.days', 30));

        $expired = $repository->expiredPending($days);

        if ($expired->isEmpty()) {
            $this->components->info('No pending referrals to expire.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($expired as $referral) {
            $repository->update($referral, [
                'status' => ReferralStatus::Expired,
            ]);
            $count++;
        }

        $this->components->info("Expired {$count} pending referral(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
