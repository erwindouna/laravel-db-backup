<?php

namespace EDouna\LaravelDBBackup\Commands;

use Carbon\Carbon;
use EDouna\LaravelDBBackup\Databases\Database;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Restore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve a list of available back-ups of the current database driver and provides a choice list.';

    /**
     * Store the main database class.
     *
     * @var Database
     */
    protected $database;

    /**
     * Create a new command instance.
     *
     * @param Database $database
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
    public function handle(): int
    {
        Log::info('Starting restore procedure.');
        $this->line('Starting restore procedure.');
        $startTime = microtime(true);

        $files = $this->getMostRecentBackups();
        if (null === $files) {
            $this->error(sprintf('No back-up files found for driver %s. No need to continue the restore procedure.', $this->databaseMain->getDatabase()->getDatabaseIdentifier()));

            return 1;
        }

        $selectionArray = [];
        foreach ($files as $k => $f) {
            $createdAt = Carbon::createFromTimestamp($f->getCTime())->toDateTimeString();
            $selectionArray[$k] = sprintf('%s | Filename: %s | Created at: %s', $k, $f->getFilename(), $createdAt);
        }

        $restoreSelection = $this->choice('Please select the desired back-up file. Use the number to continue. Default selection:', $selectionArray, 1, null, false);

        $restoreSelection = explode(' ', $restoreSelection);
        $restoreSelection = $restoreSelection[0];

        $decompressedFile = $this->database->getStorage()->decompressBackupFile($files[$restoreSelection], $this->database);
        if (false === $decompressedFile) {
            Log::error('Error in decompressing the archive. Please see the log files for further details.');
            $this->error('Error in decompressing the archive. Please see the log files for further details.');

            return 1;
        }

        if (false === $this->database->getRealDatabase()->restore($decompressedFile)) {
            Log::error('Error in restoring the database archive. Please see the log files for further details.');
            $this->error('Error in restoring the database archive. Please see the log files for further details.');

            return 1;
        }

        if (false === $this->database->getStorage()->clearTmpFile($decompressedFile)) {
            Log::error('Error in cleaning the temp back-up file. Please see the log files for further details.');
            $this->error('Error in cleaning the temp back-up file. Please see the log files for further details.');

            return 1;
        }

        $endTime = round(microtime(true) - $startTime, 2);
        $this->comment(sprintf('Finished restoring the database in %s second(s).', $endTime));
        Log::info(sprintf('Finished restoring the database in %s second(s).', $endTime));

        return 0;
    }

    /**
     * @return array|null
     */
    protected function getMostRecentBackups(): ?array
    {
        Log::debug('Searching for archived back-up files.');

        try {
            $files = File::files($this->database->getStorageFolder());
        } catch (Exception $e) {
            Log::error(sprintf('Unable to reach storage path. Exception thrown: %s', $e->getMessage()));

            return null;
        }

        // Get the files matching the database identifier
        $filesFilter = array_filter($files, function ($item) {
            return strpos($item, $this->database->getRealDatabase()->getDatabaseIdentifier());
        });

        if (null === $filesFilter || empty($filesFilter)) {
            Log::error(sprintf('No back-up files found for driver %s. No need to continue the restore procedure.', $this->database->getRealDatabase()->getDatabaseIdentifier()));

            return null;
        }

        return $filesFilter;
    }
}
