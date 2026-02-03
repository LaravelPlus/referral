<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReferralInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'email',
        'message',
        'token',
        'sent_at',
        'opened_at',
        'clicked_at',
        'accepted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Referral, $this>
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \LaravelPlus\Referral\Database\Factories\ReferralInvitationFactory
    {
        return \LaravelPlus\Referral\Database\Factories\ReferralInvitationFactory::new();
    }
}
