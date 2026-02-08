<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitApp extends Command
{
    protected $signature = 'app:init';
    protected $description = 'Initialize the application settings';

    public function handle(): int
    {
        $this->info('=== PortVPN Manager Initialization ===');
        $this->info('');

        $appName = $this->ask('Application Name', 'PortVPN Manager');
        $appUrl = $this->ask('Application URL', 'https://your-domain.com');

        // Update .env file
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        $envContent = preg_replace('/APP_NAME=.*/', 'APP_NAME="' . $appName . '"', $envContent);
        $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=' . $appUrl, $envContent);

        file_put_contents($envPath, $envContent);

        $this->info('');
        $this->info('Application initialized successfully!');
        $this->info('Name: ' . $appName);
        $this->info('URL: ' . $appUrl);
        $this->info('');
        $this->info('Please run "php artisan config:clear" to apply changes.');

        return 0;
    }
}
