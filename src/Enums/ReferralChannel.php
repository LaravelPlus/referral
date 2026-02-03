<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Enums;

enum ReferralChannel: string
{
    case Link = 'link';
    case Email = 'email';
    case Social = 'social';
}
