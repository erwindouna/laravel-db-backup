<?php

namespace EDouna\LaravelDBBackup\Exceptions;

use Exception;

class CannotCreateStorageFolderException extends Exception
{
    public static function message()
    {
        return new static('Can not create storage folder. Please check the account used to perform the command has sufficient permission(s).');
    }
}
