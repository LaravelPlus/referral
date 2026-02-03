# Referral Development Skill

## Overview
This skill activates when working with the `laravelplus/referral` package — referral codes, email invitations, and configurable rewards for Laravel.

## Package Structure
- **Config**: `config/referral.php` — codes, links, rewards, invitations, expiration, admin settings
- **Models**: `Referral`, `ReferralReward`, `ReferralInvitation`
- **Services**: `ReferralService`, `ReferralCodeService`, `ReferralRewardService`, `ReferralInvitationService`
- **Contracts**: `ReferralServiceInterface`, `ReferralRepositoryInterface`, `ReferralRewardRepositoryInterface`, `ReferralInvitationRepositoryInterface`
- **Enums**: `ReferralChannel` (Link, Email, Social), `ReferralStatus` (Pending, Completed, Rewarded, Expired), `RewardType` (Referrer, Referee), `RewardAction` (Registration, EmailVerification, OnboardingComplete, FirstActivity, FirstPurchase)
- **Actions**: `CompleteReferralAction` — call from main app flows to complete referrals
- **Controllers**: `ReferralController` (public redirect), `ReferralDashboardController`, `ReferralInvitationController`, `Admin\ReferralController`
- **Commands**: `referral:install`, `referral:cleanup`, `referral:stats`
- **Trait**: `HasReferrals` — add to User model for `referralCode()`, `referralLink()`, `referrals()`, `referredBy()`, `referralRewards()`, `getReferralStats()`

## Key Routes
- `GET /ref/{code}` — Public referral link redirect (stores code in session)
- `GET /referral/dashboard` — User referral dashboard (auth, Inertia)
- `POST /referral/invite` — Send email invitation (auth)
- `GET /admin/referrals` — Admin referral dashboard
- `GET /admin/referrals/{referral}` — Admin referral detail

## Events
- `ReferralCreated` — Dispatched when a new referral is created
- `ReferralCompleted` — Dispatched when a referral is completed
- `ReferralRewarded` — Dispatched when rewards are granted
- `InvitationSent` — Dispatched when an invitation email is sent

## Integration
To complete referrals from main app flows:
```php
app(CompleteReferralAction::class)->execute($userId, 'email_verification');
```

The `trigger` config determines which action completes referrals. Options: `registration`, `email_verification`, `onboarding_complete`, `first_activity`, `first_purchase`.

## Reward Configuration
```php
'rewards' => [
    'enabled' => true,
    'referrer_type' => 'points',
    'referrer_value' => 100,
    'referee_type' => 'points',
    'referee_value' => 50,
    'trigger' => 'email_verification',
    'max_referrals' => 0, // 0 = unlimited
],
```
