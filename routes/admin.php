<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Referral\Http\Controllers\Admin\ReferralController;

$config = config('referral.admin', []);
$prefix = $config['prefix'] ?? 'admin/referrals';
$middleware = $config['middleware'] ?? ['web', 'auth'];

Route::middleware($middleware)
    ->prefix($prefix)
    ->name('admin.referrals.')
    ->group(function (): void {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::get('{referral}', [ReferralController::class, 'show'])->name('show');
    });
