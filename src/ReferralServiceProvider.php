<?php

declare(strict_types=1);

namespace LaravelPlus\Referral;

use App\Support\AdminNavigation;
use Illuminate\Support\ServiceProvider;
use LaravelPlus\Referral\Contracts\ReferralInvitationRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralRewardRepositoryInterface;
use LaravelPlus\Referral\Contracts\ReferralServiceInterface;
use LaravelPlus\Referral\Repositories\ReferralInvitationRepository;
use LaravelPlus\Referral\Repositories\ReferralRepository;
use LaravelPlus\Referral\Repositories\ReferralRewardRepository;
use LaravelPlus\Referral\Services\ReferralCodeService;
use LaravelPlus\Referral\Services\ReferralInvitationService;
use LaravelPlus\Referral\Services\ReferralRewardService;
use LaravelPlus\Referral\Services\ReferralService;
use Throwable;

final class ReferralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/referral.php', 'referral');

        $this->app->bind(ReferralRepositoryInterface::class, ReferralRepository::class);
        $this->app->bind(ReferralRewardRepositoryInterface::class, ReferralRewardRepository::class);
        $this->app->bind(ReferralInvitationRepositoryInterface::class, ReferralInvitationRepository::class);

        $this->app->singleton(ReferralCodeService::class);
        $this->app->singleton(ReferralRewardService::class);
        $this->app->singleton(ReferralInvitationService::class);

        $this->app->singleton(ReferralServiceInterface::class, fn ($app) => new ReferralService(
            $app->make(ReferralRepositoryInterface::class),
            $app->make(ReferralCodeService::class),
            $app->make(ReferralRewardService::class),
        ));

        $this->app->singleton(ReferralService::class, fn ($app) => $app->make(ReferralServiceInterface::class));
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerResources();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerAdminNavigation();
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/referral.php' => config_path('referral.php'),
            ], 'referral-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'referral-migrations');

            $this->publishes([
                __DIR__ . '/../skills/referral-development' => base_path('.claude/skills/referral-development'),
            ], 'referral-skills');

            $this->publishes([
                __DIR__ . '/../skills/referral-development' => base_path('.github/skills/referral-development'),
            ], 'referral-skills-github');
        }
    }

    private function registerResources(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'referral');
    }

    private function registerRoutes(): void
    {
        if (!$this->isReferralEnabled()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->isAdminEnabled()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        }
    }

    /**
     * Check if the referral package is enabled via DB setting or config fallback.
     */
    private function isReferralEnabled(): bool
    {
        if (class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            try {
                $dbValue = \LaravelPlus\GlobalSettings\Models\Setting::get('package.referral.enabled');

                if ($dbValue !== null) {
                    return in_array($dbValue, ['1', 'true', true, 1], true);
                }
            } catch (Throwable) {
                // Table may not exist yet during migrations
            }
        }

        return (bool) config('referral.enabled', true);
    }

    /**
     * Check if admin routes should be enabled via DB setting or config fallback.
     */
    private function isAdminEnabled(): bool
    {
        if (class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            try {
                $dbValue = \LaravelPlus\GlobalSettings\Models\Setting::get('package.referral.enabled');

                if ($dbValue !== null) {
                    return in_array($dbValue, ['1', 'true', true, 1], true);
                }
            } catch (Throwable) {
                // Table may not exist yet during migrations
            }
        }

        return (bool) config('referral.admin.enabled', true);
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
                Console\Commands\CleanupCommand::class,
                Console\Commands\StatsCommand::class,
            ]);
        }
    }

    private function registerAdminNavigation(): void
    {
        if (!$this->isReferralEnabled()) {
            return;
        }

        $this->callAfterResolving(AdminNavigation::class, function (AdminNavigation $nav): void {
            $prefix = config('referral.admin.prefix', 'admin/referrals');

            $nav->register('referrals', 'Referrals', 'Users', [
                ['title' => 'Dashboard', 'href' => "/{$prefix}", 'icon' => 'BarChart3'],
            ], 55);
        });
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            ReferralRepositoryInterface::class,
            ReferralRewardRepositoryInterface::class,
            ReferralInvitationRepositoryInterface::class,
            ReferralServiceInterface::class,
            ReferralService::class,
            ReferralCodeService::class,
            ReferralRewardService::class,
            ReferralInvitationService::class,
        ];
    }
}
