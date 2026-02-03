<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Services;

use LaravelPlus\Referral\Models\Referral;

final class ReferralCodeService
{
    /**
     * Generate a unique referral code.
     */
    public function generate(): string
    {
        do {
            $code = $this->buildCode();
        } while (!$this->isAvailable($code));

        return $code;
    }

    /**
     * Validate that a code exists and belongs to an active referral.
     */
    public function validate(string $code): bool
    {
        return Referral::query()
            ->where('code', $code)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Resolve a referral by its code.
     */
    public function resolve(string $code): ?Referral
    {
        return Referral::query()
            ->where('code', $code)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Check if a code is available (not yet used).
     */
    public function isAvailable(string $code): bool
    {
        return !Referral::query()
            ->where('code', $code)
            ->exists();
    }

    /**
     * Build a random code string from config.
     */
    private function buildCode(): string
    {
        $prefix = (string) config('referral.code.prefix', '');
        $length = (int) config('referral.code.length', 8);
        $characters = (string) config('referral.code.characters', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789');

        $code = '';
        $charLength = mb_strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $charLength)];
        }

        return $prefix . $code;
    }
}
