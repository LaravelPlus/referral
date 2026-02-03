<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use LaravelPlus\Referral\Http\Requests\StoreInvitationRequest;
use LaravelPlus\Referral\Services\ReferralInvitationService;
use RuntimeException;

final class ReferralInvitationController
{
    public function __construct(
        private(set) ReferralInvitationService $invitationService,
    ) {}

    public function store(StoreInvitationRequest $request): RedirectResponse
    {
        try {
            $this->invitationService->send(
                $request->user()->id,
                $request->validated('email'),
                $request->validated('message'),
            );

            return back()->with('status', 'Invitation sent successfully.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }
}
