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
    protected $processHandlerMock;
    protected $testBackupFile;

    public function setUp(): void
    {
        parent::setUp();

        $this->processHandlerMock = $this->getMockBuilder('EDouna\LaravelDBBackup\ProcessHandler')->getMock();
        $this->database = new MySQLDatabase('testDatabase', 'testUser', 'testPassword', 'testHost', '3306', storage_path('db-backups'), $this->processHandlerMock);
        $this->testBackupFile = 'testBackupFile.sql';
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
        $this->processHandlerMock->method('run')->willReturn(true);

        $this->assertTrue($this->database->backup());
    }

    /**
     * @test
     */
    public function testDumpFailure()
    {
        $this->processHandlerMock->method('run')->willReturn(false);

        $this->assertFalse($this->database->backup());
    }

     /**
     * @test
     */
    public function testRestoreSuccess()
    {
        $this->processHandlerMock->method('run')->willReturn(true);

        $this->assertTrue($this->database->restore($this->testBackupFile));
    }

     /**
     * @test
     */
    public function testRestoreFailure()
    {
        $this->processHandlerMock->method('run')->willReturn(false);

        $this->assertFalse($this->database->restore($this->testBackupFile));
    }

    /**
     * @test
     */
    public function testDatabaseIdentifier()
    {
        $this->assertEquals($this->database->getDatabaseIdentifier(), 'mysql');
    }

    /**
     * @test
     */
    public function testFileExtension()
    {
        $this->assertEquals($this->database->getFileExtension(), 'sql');
    }
}
