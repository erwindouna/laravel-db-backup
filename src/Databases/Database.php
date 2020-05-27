<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\ProcessHandler;
use Illuminate\Support\Facades\Config;
use SQLiteDatabase;

class Database
{
    /**
     * @var mixed
     */
    protected $database;

    /**
     * @var array
     */
    public $realDatabase;

    protected $processHandler;

    protected $storage;

    public $backupFilename;

    protected $storageFolder;

    /**
     * @var array
     */
    protected $supportedDatabaseDrivers = ['mysql', 'sqlite'];


    public function __construct()
    {
        $this->database = Config::get('database.default');
        $this->realDatabase = Config::get('database.connections.' . $this->database);
        $this->processHandler = new ProcessHandler();

        $this->buildDatabaseClass();
    }

    public function buildDatabaseClass(): void
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

    public function setStorageFolder(string $storageFolder): void
    {
        $this->storageFolder = $storageFolder;
    }

    /**
     * @return bool
     */
    public function isDatabaseSupported(): bool
    {
        return (in_array($this->realDatabase->getDatabaseIdentifier(), $this->supportedDatabaseDrivers)) ? true : false;
    }

    /**
     * @return object
     */
    public function getRealDatabase(): object
    {
        return $this->realDatabase;
    }

    public function setBackupFilename(string $backupFilename): void
    {
        $this->backupFilename = $backupFilename;
    }

    /**
     * @return string
     */
    public function getBackupFilename(): string
    {
        return $this->backupFilename;
    }

    /**
     * @param array $database
     *
     * @return MySQLDatabase
     */
    protected function buildMySQL(array $database): MySQLDatabase
    {
        $this->database = new MySQLDatabase(
            $database['database'],
            $database['username'],
            $database['password'],
            $database['host'],
            $database['port'],
            $this->processHandler
        );

        return $this->database;
    }

    /**
     * Create an SQLite database instance.
     *
     * @param array $database
     *
     * @return Databases\SQLiteDatabase
     */
    protected function buildSQLite(array $database): SQLiteDatabase
    {
        $this->database = new SQLiteDatabase($database['database']);

        $this->generateBackupFilename();

        return $this->database;
    }
}
