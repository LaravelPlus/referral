<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Referral\Contracts\ReferralServiceInterface;

final class ReferralDashboardController
{
    public function __construct(
        private(set) ReferralServiceInterface $referralService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $stats = $this->referralService->getUserStats($user->id);

        return Inertia::render('app/Referral/Dashboard', [
            'referralCode' => $user->referralCode(),
            'referralLink' => $user->referralLink(),
            'stats' => $stats,
        ]);
    }
}
