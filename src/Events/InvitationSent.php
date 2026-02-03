<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LaravelPlus\Referral\Models\ReferralInvitation;

final class InvitationSent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ReferralInvitation $invitation
    ) {}
}
