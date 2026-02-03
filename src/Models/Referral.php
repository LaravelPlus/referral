<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelPlus\Referral\Enums\ReferralChannel;
use LaravelPlus\Referral\Enums\ReferralStatus;

final class Referral extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'referrer_id',
        'referee_id',
        'code',
        'email',
        'channel',
        'status',
        'completed_at',
        'rewarded_at',
        'expires_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => ReferralChannel::class,
            'status' => ReferralStatus::class,
            'completed_at' => 'datetime',
            'rewarded_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<\Illuminate\Foundation\Auth\User, $this>
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'referrer_id');
    }

    /**
     * @return BelongsTo<\Illuminate\Foundation\Auth\User, $this>
     */
    public function referee(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'referee_id');
    }

    /**
     * @return HasMany<ReferralReward, $this>
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    /**
     * @return HasOne<ReferralInvitation, $this>
     */
    public function invitation(): HasOne
    {
        return $this->hasOne(ReferralInvitation::class);
    }

    public function isPending(): bool
    {
        return $this->status === ReferralStatus::Pending;
    }

    public function isCompleted(): bool
    {
        return $this->status === ReferralStatus::Completed;
    }

    public function isRewarded(): bool
    {
        return $this->status === ReferralStatus::Rewarded;
    }

    public function isExpired(): bool
    {
        return $this->status === ReferralStatus::Expired;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \LaravelPlus\Referral\Database\Factories\ReferralFactory
    {
        return \LaravelPlus\Referral\Database\Factories\ReferralFactory::new();
    }
}
