<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\ProcessHandler;

interface DatabaseInterface
{
    public function backup(ProcessHandler $processHandler);

    public function restore(ProcessHandler $processHandler, string $backupFile);

    /**
     * @return string
     */
    public function getFileExtension();
}
