<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use LaravelPlus\Referral\Models\ReferralInvitation;

final class ReferralInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ReferralInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve been invited to join ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        $prefix = config('referral.link.route_prefix', 'ref');
        $code = $this->invitation->referral->code;

        return new Content(
            view: 'referral::mail.invitation',
            with: [
                'invitation' => $this->invitation,
                'referralLink' => url("/{$prefix}/{$code}"),
                'referrerName' => $this->invitation->referral->referrer->name ?? $this->invitation->referral->referrer->email,
                'appName' => config('app.name'),
                'message' => $this->invitation->message,
            ],
        );
    }
}
