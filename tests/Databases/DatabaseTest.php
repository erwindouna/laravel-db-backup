<?php

namespace EDouna\LaravelDBBackup\Databases;

use Orchestra\Testbench\TestCase;

class DatabaseTest extends TestCase
{
    protected $storageMock;
    protected $processHandlerMock;
    protected $database;

    /**
     * @environment-setup useMySQlConnection
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->database = new Database();
    }

    /**
     * @test
     */
    public function testBuildDatabaseClassMySQL()
    {
        $this->assertEquals($this->database->getRealDatabase()->getDatabaseIdentifier(), 'mysql');
    }
}
