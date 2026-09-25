# LaravelPlus Referral

Referral and invitation system for Laravel 12+. Gives every user a shareable referral code and link, sends email invitations, rewards both sides when the referred user reaches a configurable milestone, and includes a user dashboard, an admin panel and maintenance commands.

## Requirements

- PHP 8.4+
- Laravel 12 or 13

## Installation

The package is not on Packagist yet. Add the repository, then require it:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/LaravelPlus/referral" }
]
```

```bash
composer require laravelplus/referral:dev-master
```

Or keep it as a git submodule and a composer `path` repository (`packages/laravelplus/referral`).

Publish the config and migrations, then migrate:

```bash
php artisan referral:install --migrations
php artisan migrate
```

This creates `referrals`, `referral_rewards` and `referral_invitations`.

## Setup

Add the trait to your `User` model:

```php
use LaravelPlus\Referral\Traits\HasReferrals;

class User extends Authenticatable
{
    use HasReferrals;
}
```

The trait adds:

| Method | Returns |
|---|---|
| `referralCode()` | The user's code (created on first call when `code.auto_generate` is on) |
| `referralLink()` | Shareable URL, e.g. `https://example.com/ref/K7M2QX9A` |
| `referrals()` | Referrals this user created |
| `referredBy()` | The referral that brought this user in |
| `referralRewards()` | Rewards earned |
| `getReferralStats()` | Counts for dashboards |

## How a referral flows

1. A visitor opens `/ref/{code}`. The code is stored in the session as `referral_code` and the visitor is redirected to `link.redirect_to` (`/register` by default).
2. **Your registration flow** links the new user to that referral (sets `referee_id`). The package does not do this automatically, because where and how users are created differs per app.
3. When the referee performs the configured trigger action, run `CompleteReferralAction`. The referral is marked `completed`, `ReferralCompleted` is dispatched and rewards are issued.

```php
use LaravelPlus\Referral\Actions\CompleteReferralAction;
use LaravelPlus\Referral\Enums\RewardAction;

// e.g. in a listener for Illuminate\Auth\Events\Verified
app(CompleteReferralAction::class)->execute(
    $event->user->id,
    RewardAction::EmailVerification->value,
);
```

`execute()` does nothing unless the action matches `rewards.trigger`. Available actions: `registration`, `email_verification`, `onboarding_complete`, `first_activity`, `first_purchase`.

## Routes

| Method | URI | Purpose |
|---|---|---|
| GET | `/ref/{code}` | Track a referral link (prefix configurable) |
| GET | `/referral/dashboard` | User's referral dashboard (auth) |
| POST | `/referral/invite` | Send an email invitation (auth; capped at `invitation.max_per_day`) |
| GET | `/admin/referrals` | Admin list (configurable prefix + middleware) |
| GET | `/admin/referrals/{referral}` | Admin detail |

## Configuration

`config/referral.php` (publish with `--tag=referral-config`):

- **`code`** — length (8), optional prefix, alphabet (no ambiguous `0/O/1/I`), auto-generation.
- **`link`** — route prefix (`ref`) and where to redirect after tracking.
- **`rewards`** — referrer/referee reward type and value (default 100 / 50 points), `trigger` action, `max_referrals` (0 = unlimited).
- **`invitation`** — enable flag, `max_per_day` (10), `expiry_days` (30).
- **`expiration`** — optionally expire pending referrals after N days.
- **`admin`** — enable flag, route prefix (`admin/referrals`) and middleware.

## Events

`ReferralCreated`, `ReferralCompleted`, `ReferralRewarded`, `InvitationSent` — listen to these to credit balances, send notifications or log analytics.

Statuses: `pending` → `completed` → `rewarded` (or `expired`). Channels: `link`, `email`, `social`.

## Commands

| Command | Description |
|---|---|
| `referral:install` | Publish config (`--migrations`, `--skills`) |
| `referral:stats` | Show referral statistics |
| `referral:cleanup` | Expire old pending referrals (`--days=`) |

Schedule the cleanup if you enable `expiration`:

```php
Schedule::command('referral:cleanup')->daily();
```

## Publishing

| Tag | Contents |
|---|---|
| `referral-config` | `config/referral.php` |
| `referral-migrations` | Migrations |
| `referral-skills` / `referral-skills-github` | Claude Code skill for working with the package |

## Testing

The tests run inside a host Laravel app:

```bash
vendor/bin/pest packages/laravelplus/referral/tests
```

## License

MIT
