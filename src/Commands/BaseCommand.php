<?php

namespace EDouna\LaravelDBBackup\Commands;

use EDouna\LaravelDBBackup\Databases\Database;
use Illuminate\Console\Command;
use EDouna\LaravelDBBackup\Databases\Storage;

class BaseCommand extends Command
{

    protected $storage;
    protected $database;

    public function __construct()
    {
        parent::__construct();

        $this->storage = new Storage();
        $this->database = new Database();

        $this->preRun();
    }

    protected function preRun()
    {
        if (false === $this->database->isDatabaseSupported()) {
            $this->error(sprintf('The current selected %s is not supported for the back-up procedure.', $this->database->getRealDatabase()->getDatabaseIdentifier()));
            return 1;
        }

        if (false === $this->storage->initializeStorageFolder()) {
            $this->error('There was an error during initialization of the back-up directory. Please see the error log for further detais.');
            return 1;
        }

        // Set the storage folder in the database class
        $this->database->setStorageFolder($this->storage->getStorageFolder());
    }
}
