<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\Exceptions\CannotCreateStorageFolderException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


class Storage
{
    protected $database;
    protected $storagePath;

    public function __construct()
    {
        $this->storagePath = $this->sanitizeStoragePath();

        // Always check if the back-up folder is present
        $this->initializeStorageFolder();
    }

    /**
     * @return string
     */
    protected function sanitizeStoragePath(): string
    {
        $configStoragePath = Config::get('db-backup.backup_folder');
        if (substr($configStoragePath, -1, 1) !== DIRECTORY_SEPARATOR) {
            Log::info('Stored back-up folder is not probably set. Fixing.');
            $configStoragePath = $configStoragePath . DIRECTORY_SEPARATOR;
        }

        return $configStoragePath;
    }

    /**
     * @return bool
     * @throws CannotCreateStorageFolderException
     */
    protected function initializeStorageFolder(): bool
    {
        Log::debug(sprintf('Checking if storage path exists at %s', $this->storagePath));
        if (false === File::isDirectory($this->storagePath)) {
            Log::debug('Storage path does not exist. Attempting to create.');
            if (false === File::makeDirectory($this->storagePath)) {
                Log::error('Unable to create create storage path.');
                throw CannotCreateStorageFolderException::message();
            }
            Log::debug('Storage path successfully created.');
        }

        Log::debug('Finished checking storage path.');
        return true;
    }

    /**
     * @return string
     */
    public function getStorageFolder(): string
    {
        return $this->storagePath;
    }
}
