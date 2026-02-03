<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelPlus\Referral\Models\Referral;
use LaravelPlus\Referral\Models\ReferralInvitation;

/**
 * @extends Factory<ReferralInvitation>
 */
final class ReferralInvitationFactory extends Factory
{
    protected $model = ReferralInvitation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referral_id' => Referral::factory(),
            'email' => fake()->unique()->safeEmail(),
            'message' => fake()->optional()->sentence(),
            'token' => Str::random(64),
            'sent_at' => now(),
            'opened_at' => null,
            'clicked_at' => null,
            'accepted_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'accepted_at' => now(),
            'clicked_at' => now()->subMinutes(5),
        ]);
    }

    public function clicked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'clicked_at' => now(),
        ]);
    }

    public function unsent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'sent_at' => null,
        ]);
    }
}
