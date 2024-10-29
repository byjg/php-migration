<?php
namespace Tests;

use ByJG\DbMigration\Database\SqliteDatabase;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

class MigrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Migration
     */
    protected $object;

    public function setUp(): void
    {
        $this->object = new Migration(new Uri('mysql://localhost'), __DIR__ . '/dirstructure');
    }

    public function tearDown(): void
    {
        $this->object = null;
    }

    public function testGetBaseSql()
    {
        $base = $this->object->getBaseSql();
        $this->assertEquals(__DIR__ . '/dirstructure/base.sql', $base);
    }

    public function testGetBaseSqlNotFound()
    {
        $this->expectException(InvalidMigrationFile::class);
        $this->object = new Migration(new Uri('mysql://localhost'), __DIR__ . '/invalid');
        $this->object->getBaseSql();
    }

    public function testGetBaseSqlNotFoundAndNotRequired()
    {
        $this->object = new Migration(new Uri('mysql://localhost'), __DIR__ . '/invalid', false);
        $this->object->getBaseSql();
        $this->assertTrue(true);
    }

    public function testGetMigrationSql1()
    {
        $version = $this->object->getMigrationSql(1, 1);
        $this->assertEquals(__DIR__ . '/dirstructure/migrations/up/00001.sql', $version);
    }

    public function testGetMigrationSql2()
    {
        $version = $this->object->getMigrationSql(2, 1);
        $this->assertEquals(__DIR__ . '/dirstructure/migrations/up/00002.sql', $version);
    }

    public function testGetMigrationSql3()
    {
        $version = $this->object->getMigrationSql(12, 1);
        $this->assertEquals(__DIR__ . '/dirstructure/migrations/up/00012-dev.sql', $version);
    }

    public function testGetMigrationSql4()
    {
        $this->expectException(InvalidMigrationFile::class);
        $this->expectExceptionMessage("You have two files with the same version number '13'");
        $this->object->getMigrationSql(13, 1);
    }

    public function testGetMigrationSqlDown1()
    {
        $version = $this->object->getMigrationSql(1, -1);
        $this->assertEquals(__DIR__ . '/dirstructure/migrations/down/00001.sql', $version);
    }

    public function testGetMigrationSqlDown2()
    {
        $version = $this->object->getMigrationSql(2, -1);
        $this->assertEquals(__DIR__ . '/dirstructure/migrations/down/00002.sql', $version);
    }

    public function testGetMigrationSqlDown3()
    {
        $version = $this->object->getMigrationSql(12, -1);
        $this->assertEquals(__DIR__ . '/dirstructure/migrations/down/00012-dev.sql', $version);
    }

    public function testGetMigrationSqlDown4()
    {
        $this->expectException(InvalidMigrationFile::class);
        $this->expectExceptionMessage("version number '13'");
        $this->object->getMigrationSql(13, -1);
    }

    public function testGetFileContent_NonExists()
    {
        $this->assertEquals(
            [
                "file" => "non-existent",
                "description" => "no description provided. Pro tip: use `-- @description:` to define one.",
                "exists" => false,
                "checksum" => null,
                "content" => null,
            ],
            $this->object->getFileContent("non-existent")
        );
    }

    public function testGetFileContent_1()
    {
        $this->assertEquals(
            [
                "file" => __DIR__ . '/dirstructure/migrations/up/00001.sql',
                "description" => "this is a test",
                "exists" => true,
                "checksum" => "55249baf6b70c1d2e9c5362de133b2371d0dc989",
                "content" => "-- @description: this is a test\n",
            ],
            $this->object->getFileContent(__DIR__ . '/dirstructure/migrations/up/00001.sql')
        );
    }

    public function testGetFileContent_2()
    {
        $this->assertEquals(
            [
                "file" => __DIR__ . '/dirstructure/migrations/up/00002.sql',
                "description" => "another test",
                "exists" => true,
                "checksum" => "f20c73a5eb4d29e2f8edae4409a2ccc2b02c6f67",
                "content" => "--   @description:  another test\n",
            ],
            $this->object->getFileContent(__DIR__ . '/dirstructure/migrations/up/00002.sql')
        );
    }

    public function testGetFileContent_3()
    {
        $this->assertEquals(
            [
                "file" => __DIR__ . '/dirstructure/migrations/up/00003.sql',
                "description" => "no description provided. Pro tip: use `-- @description:` to define one.",
                "exists" => true,
                "checksum" => "da39a3ee5e6b4b0d3255bfef95601890afd80709",
                "content" => "",
            ],
            $this->object->getFileContent(__DIR__ . '/dirstructure/migrations/up/00003.sql')
        );
    }

    public function testReset()
    {
        $this->expectException(\PDOException::class);
        Migration::registerDatabase(SqliteDatabase::class);
        $this->object = new Migration(new Uri('sqlite:///tmp/test.db'), __DIR__ . '/dirstructure2');
        $this->object->reset();
    }

    public function testResetWithoutTransactionCheck()
    {
        try {
            Migration::registerDatabase(SqliteDatabase::class);
            $this->object = new Migration(new Uri('sqlite:///tmp/test.db'), __DIR__ . '/dirstructure2');
            $this->object->reset();
        } catch (\PDOException $ex) {
            $this->assertEquals(["version" => '2', "status" => "partial up"], $this->object->getCurrentVersion());
        }
    }

    public function testResetWithTransactionCheck()
    {
        try {
            Migration::registerDatabase(SqliteDatabase::class);
            $this->object = new Migration(new Uri('sqlite:///tmp/test.db'), __DIR__ . '/dirstructure2');
            $this->object->withTransactionEnabled();
            $this->object->reset();
        } catch (\PDOException $ex) {
            $this->assertEquals(["version" => '1', "status" => "complete"], $this->object->getCurrentVersion());
        }
    }
}
