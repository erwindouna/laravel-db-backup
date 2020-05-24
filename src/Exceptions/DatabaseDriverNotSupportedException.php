<?php

namespace EDouna\LaravelDBBackup\Exceptions;

use Exception;

class DatabaseDriverNotSupportedException extends Exception
{
    public static function message($selectedDriver)
    {
        return new static(sprintf('The current driver, %s, is not (yet) supported by the tool.', $selectedDriver));
    }
}
