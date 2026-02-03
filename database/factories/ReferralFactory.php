<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelPlus\Referral\Enums\ReferralChannel;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Models\Referral;

/**
 * @extends Factory<Referral>
 */
final class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referee_id' => null,
            'code' => Str::upper(Str::random(8)),
            'email' => null,
            'channel' => ReferralChannel::Link,
            'status' => ReferralStatus::Pending,
            'completed_at' => null,
            'rewarded_at' => null,
            'expires_at' => null,
            'metadata' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'referee_id' => User::factory(),
            'status' => ReferralStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function rewarded(): static
    {
        return $this->state(fn (array $attributes): array => [
            'referee_id' => User::factory(),
            'status' => ReferralStatus::Rewarded,
            'completed_at' => now(),
            'rewarded_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReferralStatus::Expired,
            'expires_at' => now()->subDay(),
        ]);
    }

    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes): array => [
            'email' => $email,
            'channel' => ReferralChannel::Email,
        ]);
    }

    public function withReferrer(int $userId): static
    {
        return $this->state(fn (array $attributes): array => [
            'referrer_id' => $userId,
        ]);
    }

    public function withReferee(int $userId): static
    {
        return $this->state(fn (array $attributes): array => [
            'referee_id' => $userId,
        ]);
    }
}
