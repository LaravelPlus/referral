<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Referral\Enums\ReferralStatus;
use LaravelPlus\Referral\Models\Referral;

interface ReferralRepositoryInterface
{
    public function findById(int $id): ?Referral;

    public function findByCode(string $code): ?Referral;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Referral;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Referral $referral, array $data): Referral;

    /**
     * @return Collection<int, Referral>
     */
    public function forUser(int $userId): Collection;

    public function findPendingForReferee(int $refereeId): ?Referral;

    public function findPendingByEmail(string $email): ?Referral;

    public function list(int $perPage = 15, ?string $search = null, ?ReferralStatus $status = null): LengthAwarePaginator;

    public function countByStatus(ReferralStatus $status): int;

    /**
     * @return Collection<int, Referral>
     */
    public function expiredPending(int $days): Collection;
}
