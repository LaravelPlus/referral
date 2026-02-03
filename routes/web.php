<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Referral\Http\Controllers\ReferralController;
use LaravelPlus\Referral\Http\Controllers\ReferralDashboardController;
use LaravelPlus\Referral\Http\Controllers\ReferralInvitationController;

$prefix = config('referral.link.route_prefix', 'ref');

// Public referral link
Route::middleware('web')
    ->get("/{$prefix}/{code}", ReferralController::class)
    ->name('referral.track');

// Authenticated routes
Route::middleware(['web', 'auth'])
    ->group(function (): void {
        Route::get('/referral/dashboard', ReferralDashboardController::class)
            ->name('referral.dashboard');

        Route::post('/referral/invite', [ReferralInvitationController::class, 'store'])
            ->name('referral.invite');
    });
