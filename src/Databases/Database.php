<?php

namespace EDouna\LaravelDBBackup\Databases;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Database
{
    protected $database;
    protected $storagePath;

    public function __construct()
    {
        $this->storagePath = $this->sanitizeStoragePath();

    }

    /**
     * @return string
     */
    protected function sanitizeStoragePath(): string
    {
        $configStoragePath = Config::get('db-backup.backup_folder');
        if (substr($configStoragePath, -1, 1) !== DIRECTORY_SEPARATOR) {
            Log::debug('Stored back-up folder is not probably set. Fixing.');
            $configStoragePath = $configStoragePath . DIRECTORY_SEPARATOR;
        }

        return $configStoragePath;
    }
}
