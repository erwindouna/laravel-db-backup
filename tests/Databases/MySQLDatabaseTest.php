<?php

namespace EDouna\LaravelDBBackup\Test\Databases;

use Config;
use EDouna\LaravelDBBackup\Databases\MySQLDatabase;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class MySQLDatabaseTest extends TestCase
{
    protected $database;
    protected $console;

    /**
     * @environment-setup useMySqlConnection
     * @environment-setup useStoragePath
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->database = new MySQLDatabase('testDatabase', 'testUser', 'testPassword', 'testHost', '3306', storage_path('db-backups'));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testDump()
    {
        $this->assertTrue($this->database->backup());
    }

}
