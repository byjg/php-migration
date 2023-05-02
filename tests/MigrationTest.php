<?php
namespace Test;

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
        $this->expectExceptionMessage("version number '13'");
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
                "checksum" => "b937afa57e363c9244fa30844dd11d312694f697",
                "content" => "-- @description: this is a test\n\nselect * from mysql.users;\n",
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
                "checksum" => "fd8ab8176291c2dcbf0d91564405e0f98f0cd77e",
                "content" => "--   @description:  another test\n\nselect * from dual;",
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
                "checksum" => "73faaa68e2f60c11e75a9ccc18528e0ffa15127a",
                "content" => "select something from sometable;",
            ],
            $this->object->getFileContent(__DIR__ . '/dirstructure/migrations/up/00003.sql')
        );
    }
}
