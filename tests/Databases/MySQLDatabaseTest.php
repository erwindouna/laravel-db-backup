<?php

namespace EDouna\LaravelDBBackup\Test\Databases;

use Config;
use EDouna\LaravelDBBackup\Databases\MySQLDatabase;
use Mockery as m;
use Orchestra\Testbench\TestCase;

use function PHPUnit\Framework\assertEquals;

class MySQLDatabaseTest extends TestCase
{
    protected $database;
    protected $console;

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

    /**
     * @test
     */
    public function testDumpSuccess()
    {
        $processHandler = m::mock('EDouna\LaravelDBBackup\ProcessHandler');
        $processHandler->shouldReceive('run')->andReturn(true);

        $this->assertTrue($this->database->backup());
    }

    /**
     * @test
     */
    public function testDumpFailure()
    {
        $processHandler = m::mock('EDouna\LaravelDBBackup\ProcessHandler');
        $processHandler->shouldReceive('run')->andReturn(false);

        $this->assertFalse($this->database->backup());
    }

    public function testDatabaseIdentifier()
    {
        $this->assertEquals($this->database->backup(), 'mysql');
    }
}
