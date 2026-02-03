<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelPlus\Referral\Enums\RewardType;

final class ReferralReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'user_id',
        'type',
        'reward_type',
        'reward_value',
        'granted_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RewardType::class,
            'reward_value' => 'integer',
            'granted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Referral, $this>
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * @return BelongsTo<\Illuminate\Foundation\Auth\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \LaravelPlus\Referral\Database\Factories\ReferralRewardFactory
    {
        return \LaravelPlus\Referral\Database\Factories\ReferralRewardFactory::new();
    }
}
