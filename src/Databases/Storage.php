<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\Exceptions\CannotCreateStorageFolderException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class Storage
{
    protected $database;
    protected $storagePath;
    protected $backupFilename;

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
     *
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
     * @param string $databaseIdentifier
     * @param string $databaseFileExtension
     * @return string
     */
    public function generateBackupFilename(string $databaseIdentifier, string $databaseFileExtension): string
    {
        return $this->backupFilename = $this->storagePath . $databaseIdentifier . '-' . time() . '.' . $databaseFileExtension;
    }

    /**
     * @param string $backupFile
     *
     * @param Database $database
     * @return string|string[]|null
     */
    public function decompressBackupFile(string $backupFile, Database $database)
    {
        Log::debug('Decompressing archive file.');
        $workableFile = $this->createTmpFile($backupFile, $database);

        if (null === $workableFile) {
            return null;
        }

        $process = new Process(['gzip', '-d', $workableFile]);
        $process->run(function ($type, $buffer): bool {
            if (Process::OUT === $type) {
                Log::debug('gzip buffer: ' . $buffer);
            }
            if (Process::ERR === $type) {
                Log::error('Error whilst performing zip action. Output of buffer: ' . $buffer);

                return false;
            }

            return true;
        });

        Log::debug('Finished decompressing archive file.');

        return str_replace('.gz', '', $workableFile);
    }

    /**
     * @param string $backupFile
     * @param Database $database
     * @return string|null
     */
    protected function createTmpFile(string $backupFile, Database $database): ?string
    {
        Log::debug('Creating temp back-up file to not corrupt the archives.');
        $tmpFilename = 'tmp.' . microtime(true) . '.' . $database->getRealDatabase()->getFileExtension() . '.gz';
        $filePath = $this->storagePath . $tmpFilename;

        try {
            File::copy($backupFile, $filePath);
        } catch (Exception $e) {
            Log::error('Could not create temporary archive file. Exception throw: ' . $e->getMessage());

            return null;
        }

        Log::debug('Finished creating temp back-up file.');

        return $filePath;
    }

    /**
     * @param string $backupFile
     *
     * @return bool
     */
    public function clearTmPFile(string $backupFile): bool
    {
        Log::debug('Cleaning up temp back-up file.');

        try {
            File::delete($backupFile);
        } catch (Exception $e) {
            Log::error(sprintf('Could not clean up temp back-up file. Exception thrown: %s', $e->getMessage()));

            return false;
        }

        Log::debug('Finished cleaning up temp back-up file.');

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
