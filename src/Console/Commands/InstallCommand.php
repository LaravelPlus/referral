<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Console\Commands;

use Illuminate\Console\Command;

final class InstallCommand extends Command
{
    protected $signature = 'referral:install
        {--migrations : Publish migrations}
        {--skills : Publish Claude skills}';

    protected $description = 'Install the Referral package assets and configuration.';

    public function handle(): int
    {
        $this->info('Installing LaravelPlus Referral...');

        $this->call('vendor:publish', [
            '--tag' => 'referral-config',
            '--force' => false,
        ]);

        if ($this->option('migrations')) {
            $this->call('vendor:publish', [
                '--tag' => 'referral-migrations',
                '--force' => false,
            ]);
        }

        if ($this->option('skills')) {
            $this->call('vendor:publish', [
                '--tag' => 'referral-skills',
                '--force' => false,
            ]);
        }

        $this->newLine();
        $this->components->info('Referral package installed successfully.');
        $this->newLine();

        $this->line('Add the <fg=green>HasReferrals</> trait to your User model:');
        $this->newLine();
        $this->line('  <fg=green>use LaravelPlus\Referral\Traits\HasReferrals;</>');
        $this->newLine();

        $this->line('Run migrations:');
        $this->line('  <fg=green>php artisan migrate</>');
        $this->newLine();

        $this->line('To view referral stats:');
        $this->line('  <fg=green>php artisan referral:stats</>');

        return self::SUCCESS;
    }
}
