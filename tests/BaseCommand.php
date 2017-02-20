<?php
/**
 * User: jg
 * Date: 19/02/17
 * Time: 19:52
 */


use ByJG\DbMigration\Commands\SqliteCommand;


abstract class BaseCommand extends PHPUnit_Framework_TestCase
{
    protected $uri = null;

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        // create Migrate object in the parent!!!

        $this->migrate->prepareEnvironment();
    }

    public function tearDown()
    {
        $this->migrate->getDbCommand()->dropDatabase();
    }

    public function testVersion0()
    {
        $this->migrate->reset(0);
        $this->assertVersion0();
    }

    public function testUpVersion1()
    {
        $this->migrate->reset(0);
        $this->assertVersion0();
        $this->migrate->up(1);
        $this->assertVersion1();
    }

    public function testUpVersion2()
    {
        $this->migrate->reset(0);
        $this->assertVersion0();
        $this->migrate->up(2);
        $this->assertVersion2();
    }

    public function testDownVersion1()
    {
        $this->migrate->reset();
        $this->assertVersion2();
        $this->migrate->down(1);
        $this->assertVersion1();
    }

    public function testDownVersion0()
    {
        $this->migrate->reset();
        $this->assertVersion2();
        $this->migrate->down(0);
        $this->assertVersion0();
    }

    protected function getExpectedUsersVersion0()
    {
        return [
            ["id" => 1, "name" => 'John Doe', 'createdate' => '20160110'],
            ["id" => 2, "name" => 'Jane Doe', 'createdate' => '20151230']
        ];
    }

    protected function getExpectedUsersVersion1()
    {
        return [
            ["id" => 1, "name" => 'John Doe', 'createdate' => '2016-01-10'],
            ["id" => 2, "name" => 'Jane Doe', 'createdate' => '2015-12-30']
        ];
    }

    protected function assertVersion0()
    {
        $version = $this->migrate->getDbDriver()->getScalar('select version from migration_version');
        $this->assertEquals(0, $version);

        $iterator = $this->migrate->getDbDriver()->getIterator('select * from users');

        $this->assertTrue($iterator->hasNext());
        $row = $iterator->moveNext();
        $this->assertEquals(
            $this->getExpectedUsersVersion0()[0],
            $row->toArray()
        );

        $this->assertTrue($iterator->hasNext());
        $row = $iterator->moveNext();
        $this->assertEquals(
            $this->getExpectedUsersVersion0()[1],
            $row->toArray()
        );

        $this->assertFalse($iterator->hasNext());

        try {
            $this->migrate->getDbDriver()->getIterator('select * from roles');
        } catch (PDOException $ex) {
            $this->assertTrue(true);
        }
    }

    protected function assertVersion1()
    {
        $version = $this->migrate->getDbDriver()->getScalar('select version from migration_version');
        $this->assertEquals(1, $version);

        $iterator = $this->migrate->getDbDriver()->getIterator('select * from users');

        $this->assertTrue($iterator->hasNext());
        $row = $iterator->moveNext();
        $this->assertEquals(
            $this->getExpectedUsersVersion1()[0],
            $row->toArray()
        );

        $this->assertTrue($iterator->hasNext());
        $row = $iterator->moveNext();
        $this->assertEquals(
            $this->getExpectedUsersVersion1()[1],
            $row->toArray()
        );

        $this->assertFalse($iterator->hasNext());

        try {
            $this->migrate->getDbDriver()->getIterator('select * from roles');
        } catch (PDOException $ex) {
            $this->assertTrue(true);
        }
    }

    protected function assertVersion2()
    {
        $version = $this->migrate->getDbDriver()->getScalar('select version from migration_version');
        $this->assertEquals(2, $version);

        $iterator = $this->migrate->getDbDriver()->getIterator('select * from users');

        $this->assertTrue($iterator->hasNext());
        $row = $iterator->moveNext();
        $this->assertEquals(
            $this->getExpectedUsersVersion1()[0],
            $row->toArray()
        );

        $this->assertTrue($iterator->hasNext());
        $row = $iterator->moveNext();
        $this->assertEquals(
            $this->getExpectedUsersVersion1()[1],
            $row->toArray()
        );

        $this->assertFalse($iterator->hasNext());

        $this->migrate->getDbDriver()->getIterator('select * from roles');
    }
}
