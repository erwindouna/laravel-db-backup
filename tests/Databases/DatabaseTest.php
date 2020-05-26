<?php

namespace EDouna\LaravelDBBackup\Databases;

use Orchestra\Testbench\TestCase;

class DatabaseTest extends TestCase
{
    protected $storage;
    protected $processHandler;
    protected $database;

    public function setUp(): void
    {
        $this->storage = $this->getMockBuilder('EDouna\LaravelDBBackup\Database\Storage')->getMock();
        $this->processHandlerMock = $this->getMockBuilder('EDouna\LaravelDBBackup\ProcessHandler')->getMock();

       $this->database = new Database($this->storage);
    }
}
