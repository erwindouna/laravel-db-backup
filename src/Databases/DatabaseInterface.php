<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\ProcessHandler;

interface DatabaseInterface
{
    public function backup();

    public function restore(string $backupFile);

    public function getFileExtension();

    public function getDatabaseIdentifier();
}
