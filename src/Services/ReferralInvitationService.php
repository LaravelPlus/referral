<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use LaravelPlus\Referral\Contracts\ReferralInvitationRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralServiceInterface;
use LaravelPlus\Referral\Events\InvitationSent;
use LaravelPlus\Referral\Mail\ReferralInvitationMail;
use LaravelPlus\Referral\Models\ReferralInvitation;
use RuntimeException;

final class ReferralInvitationService
{
    public function __construct(
        private readonly ReferralInvitationRepositoryInterface $repository,
        private readonly ReferralServiceInterface $referralService,
    ) {}

    /**
     * Send an invitation email.
     */
    public function send(int $userId, string $email, ?string $message = null): ReferralInvitation
    {
        if (!config('referral.invitation.enabled', true)) {
            throw new RuntimeException('Invitations are disabled.');
        }

        $maxPerDay = (int) config('referral.invitation.max_per_day', 10);
        $sentToday = $this->repository->countSentTodayByUser($userId);

        if ($sentToday >= $maxPerDay) {
            throw new RuntimeException("Daily invitation limit ({$maxPerDay}) reached.");
        }

        $referral = $this->referralService->createForUser($userId, $email, 'email');

        $invitation = $this->repository->create([
            'referral_id' => $referral->id,
            'email' => $email,
            'message' => $message,
            'token' => Str::random(64),
            'sent_at' => now(),
        ]);

        Mail::to($email)->send(new ReferralInvitationMail($invitation));

        InvitationSent::dispatch($invitation);

        return $invitation;
    }

    /**
     * Resend an existing invitation.
     */
    public function resend(ReferralInvitation $invitation): ReferralInvitation
    {
        $invitation = $this->repository->update($invitation, [
            'sent_at' => now(),
        ]);

        Mail::to($invitation->email)->send(new ReferralInvitationMail($invitation));

        InvitationSent::dispatch($invitation);

        return $invitation;
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(ReferralInvitation $invitation): void
    {
        $this->repository->update($invitation, [
            'accepted_at' => null,
        ]);
    }

    /**
     * Accept an invitation by token.
     */
    public function accept(string $token): ?ReferralInvitation
    {
        $invitation = $this->repository->findByToken($token);

        if (!$invitation || $invitation->isAccepted()) {
            return null;
        }

        return $this->repository->update($invitation, [
            'accepted_at' => now(),
        ]);
    }

    /**
     * Track when an invitation link is clicked.
     */
    public function trackClick(string $token): ?ReferralInvitation
    {
        $invitation = $this->repository->findByToken($token);

        if (!$invitation) {
            return null;
        }

        if (!$invitation->clicked_at) {
            $invitation = $this->repository->update($invitation, [
                'clicked_at' => now(),
            ]);
        }

        return $invitation;
    }
}
