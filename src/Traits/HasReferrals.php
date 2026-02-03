<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Models\Referral;
use LaravelPlus\Referral\Models\ReferralReward;
use LaravelPlus\Referral\Services\ReferralCodeService;

trait HasReferrals
{
    /**
     * Get or create the user's referral code.
     */
    public function referralCode(): string
    {
        $referral = $this->referrals()
            ->whereNull('referee_id')
            ->whereNull('email')
            ->first();

        if ($referral) {
            return $referral->code;
        }

        if (config('referral.code.auto_generate', true)) {
            $codeService = app(ReferralCodeService::class);
            $code = $codeService->generate();

            $referral = $this->referrals()->create([
                'code' => $code,
                'channel' => 'link',
                'status' => ReferralStatus::Pending,
            ]);

            return $referral->code;
        }

        return '';
    }

    /**
     * Get the user's shareable referral link.
     */
    public function referralLink(): string
    {
        $prefix = config('referral.link.route_prefix', 'ref');

        return url("/{$prefix}/{$this->referralCode()}");
    }

    /**
     * Get all referrals created by this user.
     *
     * @return HasMany<Referral, $this>
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get the referral that brought this user.
     *
     * @return HasOne<Referral, $this>
     */
    public function referredBy(): HasOne
    {
        return $this->hasOne(Referral::class, 'referee_id');
    }

    /**
     * Get all referral rewards for this user.
     *
     * @return HasMany<ReferralReward, $this>
     */
    public function referralRewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'user_id');
    }

    /**
     * Get referral statistics for this user.
     *
     * @return array{total: int, pending: int, completed: int, rewarded: int}
     */
    public function getReferralStats(): array
    {
        $referrals = $this->referrals()->get();

        return [
            'total' => $referrals->count(),
            'pending' => $referrals->where('status', ReferralStatus::Pending)->count(),
            'completed' => $referrals->where('status', ReferralStatus::Completed)->count(),
            'rewarded' => $referrals->where('status', ReferralStatus::Rewarded)->count(),
        ];
    }
}
