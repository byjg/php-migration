<?php
namespace Test;

use ByJG\AnyDataset\Store\PdoMysql;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class MigrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Migration
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Migration(new Uri('mysql://localhost'), __DIR__ . '/dirstructure');
    }

    public function tearDown()
    {
        $this->object = null;
    }

    public function testGetBaseSql()
    {
        $base = $this->object->getBaseSql();
        $this->assertEquals(__DIR__ . '/dirstructure/base.sql', $base);
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

    /**
     * @expectedException \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @expectedExceptionMessage version number '13'
     */
    public function testGetMigrationSql4()
    {
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

    /**
     * @expectedException \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @expectedExceptionMessage version number '13'
     */
    public function testGetMigrationSqlDown4()
    {
        $this->object->getMigrationSql(13, -1);
    }
}
