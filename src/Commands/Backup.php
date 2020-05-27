<?php

namespace EDouna\LaravelDBBackup\Commands;

use Illuminate\Support\Facades\Log;

class Backup extends BaseCommand
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        Log::info('Starting back-up procedure.');
        $this->line('Starting back-up procedure.');
        $startTime = microtime(true);

        $this->database->buildDatabaseClass();

        if (false === $this->database->isDatabaseSupported()) {
            $this->error(sprintf('The current selected %s database driver is not (yet) supported.', $this->database->getRealDatabase()->getDatabaseIdentifier()));

            return 1;
        }

        if (false === $this->storage->initializeStorageFolder()) {
            $this->error('Error in the back-up directory. Please see the error log for further details.');

            return 1;
        }

        $this->database->setBackupFilename($this->storage->generateBackupFilename($this->database->getRealDatabase()->getDatabaseIdentifier(), $this->database->getRealDatabase()->getFileExtension()));

        $this->comment(sprintf('Current selected database driver: %s', $this->database->getRealDatabase()->getDatabaseIdentifier()));
        $this->comment('Creating back-up. Depending on the database size, this might take few moments...');
        // Run the back-up
        if (false === $this->database->getRealDatabase()->backup($this->database->getBackupFilename())) {
            $this->error('Error while performing back-up. Please find the error log for further details.');

            return 1;
        }

        $this->comment('Done creating back-up! Archiving the database...');
        if (false === $this->storage->createArchiveFile($this->database->getBackupFilename())) {
            $this->error('Error while creating the archive file. Please find the error log for further details.');

            return 1;
        }

        $this->comment('Done creating archive!');
        $endTime = round(microtime(true) - $startTime, 2);
        $this->line(sprintf('Finished back-up procedure in %s second(s).', $endTime));

        return 0;
    }
}
