<?php

namespace EDouna\LaravelDBBackup\Databases;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

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
    public function __construct(string $database, string $user, string $password, string $host, string $port, string $storageFolder)
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
    }

    /**
     * @return string
     */
    public function getBackupFilename(): string
    {
        return $this->backupFilename;
    }

    /**
     * @return bool
     */
    public function backup(): bool
    {
        Log::debug('Start creating MySQL dump file.');
        $this->createBackupFilename();

        //$storageFilepath = '"' . addcslashes($this->storageFolder, '\\"') . '"';
        $command = sprintf('mysqldump %s --skip-comments %s > %s', $this->getCredentials(), $this->database, $this->backupFilename);

        $process = Process::fromShellCommandline($command, null, null, null, 9999.00);

        $processFailure = false;
        $process->run(function ($type, $buffer) use ($processFailure): bool {
            if (Process::OUT === $type) {
                Log::debug('MySQL Dump buffer: '.$buffer);
            }
            if (Process::ERR === $type) {
                if (!strpos($buffer, '[Warning]')) {
                    Log::error('Error whilst performing mysqldump. Dump stopped. Output of buffer: '.$buffer);
                    $processFailure = true;
                }
            }

            return $processFailure;
        });

        if (true === $processFailure) {
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
        $this->backupFilename = $this->storageFolder.$this->getDatabaseIdentifier().'-'.microtime(true).'.'.$this->getFileExtension();
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

    public function restore(string $backupFile): bool
    {
        Log::debug('Starting MySQL import procedure.');

        $startTimeImport = microtime(true);
        $backupFile = '"'.addcslashes($backupFile, '\\"').'"';
        $command = sprintf('mysql %s %s < %s', $this->getCredentials(), $this->database, $backupFile);

        $process = Process::fromShellCommandline($command, null, null, null, 9999.00);

        $process->run(function ($type, $buffer): bool {
            if (Process::OUT === $type) {
                Log::debug('MySQL import buffer: '.$buffer);
            }
            if (Process::ERR === $type) {
                if (strpos($buffer, '[Warning]')) {
                    return false;
                }
                Log::error('Error whilst performing mysql import. Import stopped. Output of buffer: '.$buffer);

                return false;
            }

            return true;
        });

        $endTimeImport = round(microtime(true) - $startTimeImport, 2);
        Log::debug(sprintf('Import successfully run in %s second(s).', $endTimeImport));

        return true;
    }
}
