<?php

namespace EDouna\LaravelDBBackup;

use EDouna\LaravelDBBackup\Commands\Backup;
use Illuminate\Support\ServiceProvider;

class LaravelDBBackupProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register the config
        $this->mergeConfigFrom(__DIR__ . '/../config/db-backup.php', 'db-backup');

        // Register the commands
        $this->commands([
            Backup::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Initialize the config
        $this->publishes([
            __DIR__ . '/../config/db-backup.php' => config_path('db-backup.php')
        ], 'config');

    }
}
