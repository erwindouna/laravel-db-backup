<?php

namespace EDouna\LaravelDBBackup\Test\Databases;

use Config;
use EDouna\LaravelDBBackup\Databases\MySQLDatabase;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class MySQLDatabaseTest extends TestCase
{
    protected $database;
    protected $storageMock;
    protected $processHandlerMock;
    protected $testBackupFile;

    public function setUp(): void
    {
        parent::setUp();

        $this->storageMock = $this->getMockBuilder('EDouna\LaravelDBBackup\Databases\Storage')->getMock();
        $this->storageMock = m::mock('EDouna\LaravelDBBackup\Databases\Storage');
        $this->processHandlerMock = $this->getMockBuilder('EDouna\LaravelDBBackup\ProcessHandler')->getMock();
        $this->database = new MySQLDatabase('testDatabase', 'testUser', 'testPassword', 'testHost', '3306', $this->processHandlerMock, $this->storageMock);
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

        $this->assertTrue($this->database->backup($this->testBackupFile));
    }

    /**
     * @test
     */
    public function testDumpFailure()
    {
        $this->processHandlerMock->method('run')->willReturn(false);

        $this->assertFalse($this->database->backup($this->testBackupFile));
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
