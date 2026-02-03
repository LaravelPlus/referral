<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use LaravelPlus\Referral\Services\ReferralCodeService;

final class ReferralController
{
    public function __construct(
        private(set) ReferralCodeService $codeService,
    ) {}

    /**
     * Handle a referral link visit. Store the code in session and redirect to registration.
     */
    public function __invoke(string $code): RedirectResponse
    {
        $referral = $this->codeService->resolve($code);

        if (!$referral) {
            return redirect(config('referral.link.redirect_to', '/register'));
        }

        session()->put('referral_code', $referral->code);

        return redirect(config('referral.link.redirect_to', '/register'));
    }
}
