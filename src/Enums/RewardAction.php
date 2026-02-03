<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Enums;

enum RewardAction: string
{
    case Registration = 'registration';
    case EmailVerification = 'email_verification';
    case OnboardingComplete = 'onboarding_complete';
    case FirstActivity = 'first_activity';
    case FirstPurchase = 'first_purchase';
}
