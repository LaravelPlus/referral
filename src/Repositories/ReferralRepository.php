<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Referral\Contracts\ReferralRepositoryInterface;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Models\Referral;

final class ReferralRepository implements ReferralRepositoryInterface
{
    public private(set) string $modelClass = Referral::class;

    public function findById(int $id): ?Referral
    {
        return $this->modelClass::query()->find($id);
    }

    public function findByCode(string $code): ?Referral
    {
        return $this->modelClass::query()
            ->where('code', $code)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Referral
    {
        return $this->modelClass::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Referral $referral, array $data): Referral
    {
        $referral->update($data);

        return $referral->fresh();
    }

    /**
     * @return Collection<int, Referral>
     */
    public function forUser(int $userId): Collection
    {
        return $this->modelClass::query()
            ->where('referrer_id', $userId)
            ->latest()
            ->get();
    }

    public function findPendingForReferee(int $refereeId): ?Referral
    {
        return $this->modelClass::query()
            ->where('referee_id', $refereeId)
            ->where('status', ReferralStatus::Pending)
            ->first();
    }

    public function findPendingByEmail(string $email): ?Referral
    {
        return $this->modelClass::query()
            ->where('email', $email)
            ->where('status', ReferralStatus::Pending)
            ->first();
    }

    public function list(int $perPage = 15, ?string $search = null, ?ReferralStatus $status = null): LengthAwarePaginator
    {
        $query = $this->modelClass::query()
            ->with(['referrer', 'referee'])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('referrer', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function countByStatus(ReferralStatus $status): int
    {
        return $this->modelClass::query()
            ->where('status', $status)
            ->count();
    }

    /**
     * @return Collection<int, Referral>
     */
    public function expiredPending(int $days): Collection
    {
        return $this->modelClass::query()
            ->where('status', ReferralStatus::Pending)
            ->where('created_at', '<', now()->subDays($days))
            ->get();
    }
}
