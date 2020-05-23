<?php

namespace EDouna\LaravelDBBackup\Commands;

use EDouna\LaravelDBBackup\Databases\Database;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Backup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempts to run a database back-up procedure.';

    /**
     * Store the main database class
     * @var Database
     */
    protected $database;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('Starting back-up procedure.');
        $this->comment('Starting back-up procedure.');
        $startTime = microtime(true);


    }
}