<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\Exceptions\DatabaseDriverNotSupportedException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use SQLiteDatabase;
use Symfony\Component\Process\Process;

class Database
{

    /**
     * @var mixed
     */
    protected $database;

    /**
     * @var array
     */
    protected $realDatabase;

    /**
     * @var array
     */
    protected $supportedDatabaseDrivers = ['mysql', 'sqlite'];

    protected $storageFolder;

    public function __construct(Storage $storage)
    {
        $this->database = Config::get('database.default');
        $this->realDatabase = Config::get('database.connections.' . $this->database);
        $this->storageFolder = $storage->getStorageFolder();

        // Check if the current database driver is supported
        $this->initSupportedDatabaseDrivers();
        $this->buildDatabaseClass();
    }

    protected function buildDatabaseClass(): void
    {
        switch ($this->realDatabase['driver']) {
            case 'mysql':
                $this->realDatabase = $this->buildMySQL($this->realDatabase);
                break;
            case 'sqlite':
                $this->realDatabase = $this->buildSQLite($this->realDatabase);
                break;
        }
    }

    /**
     * @return object
     */
    public function getRealDatabase(): object
    {
        return $this->realDatabase;
    }

    /**
     * @return bool
     */
    public function createArchiveFile(): bool
    {
        Log::debug('Trying to start creating an archive file');
        $process = new Process(['gzip', '-9', $this->getRealDatabase()->getBackupFilename()]);

        $processFailure = false;
        $process->run(function ($type, $buffer) use ($processFailure): bool {
            if (Process::OUT === $type) {
                Log::debug('gzip buffer: ' . $buffer);
            }
            if (Process::ERR === $type) {
                Log::error('Error whilst performing zip action. Output of buffer: ' . $buffer);
                $processFailure = true;
            }

            return $processFailure;
        });

        if (true === $processFailure) {
            return false;
        }

        Log::debug('Finished creating an archive file.');
        return true;
    }

    /**
     * @param array $database
     * @return MySQLDatabase
     */
    protected function buildMySQL(array $database): MySQLDatabase
    {
        $this->database = new MySQLDatabase(
            $database['database']
            , $database['username']
            , $database['password']
            , $database['host']
            , $database['port']
            , $this->storageFolder
        );

        // Generate a unique filename
        $this->backupFilename = $this->storageFolder . $database['driver'] . '-' . microtime(true) . '.' . $this->database->getFileExtension();

        return $this->database;
    }

    /**
     * Create an SQLite database instance
     *
     * @param array $database
     * @return Databases\SQLiteDatabase
     */
    protected function buildSQLite(array $database): SQLiteDatabase
    {
        $this->database = new SQLiteDatabase($database['database']);

        // Generate a unique filename
        $this->backupFilename = $this->storagePath . 'sqlite-' . microtime(true) . '.' . $this->database->getFileExtension();

        return $this->database;
    }

    /**
     * @throws DatabaseDriverNotSupportedException
     */
    protected function initSupportedDatabaseDrivers(): void
    {
        if (!in_array($this->realDatabase['driver'], $this->supportedDatabaseDrivers)) {
            throw DatabaseDriverNotSupportedException::message($this->realDatabase['driver']);
        }
    }
}
