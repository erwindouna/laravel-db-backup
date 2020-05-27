<?php

namespace EDouna\LaravelDBBackup\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Restore extends BaseCommand
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        Log::info('Starting restore procedure.');
        $this->line('Starting restore procedure.');
        $startTime = microtime(true);

        $this->database->buildDatabaseClass();

        if (false === $this->storage->initializeStorageFolder()) {
            $this->error('Error in the back-up directory. Please see the error log for further details.');

            return 1;
        }

        $files = $this->storage->getMostRecentBackups($this->database->getRealDatabase()->getDatabaseIdentifier());
        if (null === $files) {
            $this->error(sprintf('No back-up files found for driver %s. No need to continue the restore procedure.', $this->database->getRealDatabase()->getDatabaseIdentifier()));

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

        $this->comment('Restoring back-up. Depending on the file size this might take a few moments...');

        $decompressedFile = $this->storage->decompressBackupFile($files[$restoreSelection], $this->database);
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

        if (false === $this->storage->clearTmpFile($decompressedFile)) {
            Log::error('Error in cleaning the temp back-up file. Please see the log files for further details.');
            $this->error('Error in cleaning the temp back-up file. Please see the log files for further details.');

            return 1;
        }

        $endTime = round(microtime(true) - $startTime, 2);
        $this->comment(sprintf('Finished restoring the database in %s second(s).', $endTime));
        Log::info(sprintf('Finished restoring the database in %s second(s).', $endTime));

        return 0;
    }
}
