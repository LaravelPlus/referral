<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Enums;

enum RewardType: string
{
    case Referrer = 'referrer';
    case Referee = 'referee';
}
