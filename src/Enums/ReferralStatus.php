<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Enums;

enum ReferralStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Rewarded = 'rewarded';
    case Expired = 'expired';
}
