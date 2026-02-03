<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Referral\Contracts\ReferralRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralRewardRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralServiceInterface;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Models\Referral;

final class ReferralController
{
    public function __construct(
        private(set) ReferralServiceInterface $referralService,
        private(set) ReferralRepositoryInterface $referralRepository,
        private(set) ReferralRewardRepositoryInterface $rewardRepository,
    ) {}

    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        if (!$user || !array_any(['super-admin', 'admin'], fn (string $role): bool => $user->hasRole($role))) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }

    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $status = $request->get('status')
            ? ReferralStatus::tryFrom($request->get('status'))
            : null;

        $referrals = $this->referralService->list(
            perPage: 15,
            search: $request->get('search'),
            status: $status,
        );

        return Inertia::render('admin/Referrals/Index', [
            'referrals' => $referrals,
            'stats' => [
                'total_pending' => $this->referralRepository->countByStatus(ReferralStatus::Pending),
                'total_completed' => $this->referralRepository->countByStatus(ReferralStatus::Completed),
                'total_rewarded' => $this->referralRepository->countByStatus(ReferralStatus::Rewarded),
                'total_rewards_granted' => $this->rewardRepository->count(),
            ],
            'filters' => [
                'search' => $request->get('search', ''),
                'status' => $request->get('status', ''),
            ],
        ]);
    }

    public function show(Referral $referral): Response
    {
        $this->authorizeAdmin();

        $referral->load(['referrer', 'referee', 'rewards', 'invitation']);

        return Inertia::render('admin/Referrals/Show', [
            'referral' => $referral,
        ]);
    }
}
