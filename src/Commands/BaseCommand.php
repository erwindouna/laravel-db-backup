<?php

namespace EDouna\LaravelDBBackup\Commands;

use EDouna\LaravelDBBackup\Databases\Database;
use EDouna\LaravelDBBackup\Databases\Storage;
use Illuminate\Console\Command;

class BaseCommand extends Command
{
    protected $storage;
    protected $database;

    /**
     * BaseCommand constructor.
     * @param Storage $storage
     * @param Database $database
     */
    public function __construct(Storage $storage, Database $database)
    {
        parent::__construct();

        $this->storage = $storage;
        $this->database = $database;

        //$this->preRun();
    }
}
