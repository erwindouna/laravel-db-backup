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

    }
}
