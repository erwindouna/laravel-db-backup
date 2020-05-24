<?php

namespace EDouna\LaravelDBBackup\Databases;

interface DatabaseInterface
{
    public function backup();

    public function restore(string $backupFile);

    /**
     * @return string
     */
    public function getFileExtension();

}
