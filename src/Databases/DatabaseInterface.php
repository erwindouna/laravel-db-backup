<?php

namespace EDouna\LaravelDBBackup\Databases;

interface DatabaseInterface
{
    public function backup(string $backupFile);

    public function restore(string $backupFile);

    public function getFileExtension();

    public function getDatabaseIdentifier();
}
