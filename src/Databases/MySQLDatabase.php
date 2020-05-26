<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\ProcessHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\ProcessHelper;

class MySQLDatabase implements DatabaseInterface
{
    protected $database;
    protected $user;
    protected $password;
    protected $host;
    protected $port;
    protected $storageFolder;
    protected $backupFilename;
    protected $fileExtension;
    protected $databaseIdentifier;
    protected $processHandler;

    /**
     * MySQLDatabase constructor.
     *
     * @param string $database
     * @param string $user
     * @param string $password
     * @param string $host
     * @param string $port
     * @param string $storageFolder
     */
    public function __construct(string $database, string $user, string $password, string $host, string $port, string $storageFolder, ProcessHandler $processHandler)
    {
        Log::debug('Constructing MySQL database class.');
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;

        $this->storageFolder = $storageFolder;

        $this->fileExtension = 'sql';
        $this->databaseIdentifier = 'mysql';

        $this->processHandler = $processHandler;
    }

    /**
     * @return string
     */
    public function getBackupFilename(): string
    {
        return $this->backupFilename;
    }

    /**
     *
     * @return bool
     */
    public function backup(): bool
    {
        Log::debug('Start creating MySQL dump file.');
        $this->createBackupFilename();

        //$storageFilepath = '"' . addcslashes($this->storageFolder, '\\"') . '"';
        $command = sprintf('mysqldump %s --skip-comments %s > %s', $this->getCredentials(), $this->database, $this->backupFilename);

        if (false === $this->processHandler->run($command)) {
            return false;
        }

        Log::debug('Finished running MySQL dump.');

        return true;
    }

    /**
     * Generate a unique back-up filename.
     */
    protected function createBackupFilename(): void
    {
        $this->backupFilename = $this->storageFolder . $this->getDatabaseIdentifier() . '-' . microtime(true) . '.' . $this->getFileExtension();
    }

    /**
     * @return string
     */
    public function getDatabaseIdentifier(): string
    {
        return $this->databaseIdentifier;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    /**
     * Dedicated string to be used when performing MySQL commands.
     *
     * @return string
     */
    protected function getCredentials(): string
    {
        return sprintf('--user=%s --password=%s --host=%s --port=%s', $this->user, $this->password, $this->host, $this->port);
    }

    /**
     * @param ProcessHandler $processHandler
     * @param string $backupFile
     * @return bool
     */
    public function restore(string $backupFile): bool
    {
        Log::debug('Starting MySQL import procedure.');

        $startTimeImport = microtime(true);
        $backupFile = '"' . addcslashes($backupFile, '\\"') . '"';
        $command = sprintf('mysql %s %s < %s', $this->getCredentials(), $this->database, $backupFile);
        
        if (false === $this->processHandler->run($command)) {
            return false;
        }

        $endTimeImport = round(microtime(true) - $startTimeImport, 2);
        Log::debug(sprintf('Import successfully run in %s second(s).', $endTimeImport));

        return true;
    }
}
