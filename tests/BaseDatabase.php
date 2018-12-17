<?php

use ByJG\DbMigration\Migration;

abstract class BaseDatabase extends \PHPUnit\Framework\TestCase
{
    protected $uri = null;

    /**
     * @var Migration
     */
    protected $migrate = null;

    protected $migrationTable = "migration_version";

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     */
    public function setUp()
    {
        // create Migrate object in the parent!!!

        $this->migrate->prepareEnvironment();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     */
    public function tearDown()
    {
        $this->migrate->getDbCommand()->dropDatabase();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function testVersion0()
    {
        $this->migrate->reset(0);
        $this->assertVersion0();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function testUpVersion1()
    {
        $this->migrate->reset(0);
        $this->assertVersion0();
        $this->migrate->up(1);
        $this->assertVersion1();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function testUpVersion2()
    {
        $this->migrate->reset(0);
        $this->assertVersion0();
        $this->migrate->up(2);
        $this->assertVersion2();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function testDownVersion1()
    {
        $this->migrate->reset();
        $this->assertVersion2();
        $this->migrate->down(1);
        $this->assertVersion1();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
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

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    protected function assertVersion0()
    {
        $version = $this->migrate->getDbDriver()->getScalar('select version from '. $this->migrationTable);
        $this->assertEquals(0, $version);
        $status = $this->migrate->getDbDriver()->getScalar('select status from '. $this->migrationTable);
        $this->assertEquals(Migration::VERSION_STATUS_COMPLETE, $status);

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

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    protected function assertVersion1()
    {
        $version = $this->migrate->getDbDriver()->getScalar('select version from '. $this->migrationTable);
        $this->assertEquals(1, $version);
        $status = $this->migrate->getDbDriver()->getScalar('select status from '. $this->migrationTable);
        $this->assertEquals(Migration::VERSION_STATUS_COMPLETE, $status);

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

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    protected function assertVersion2()
    {
        $version = $this->migrate->getDbDriver()->getScalar('select version from '. $this->migrationTable);
        $this->assertEquals(2, $version);
        $status = $this->migrate->getDbDriver()->getScalar('select status from '. $this->migrationTable);
        $this->assertEquals(Migration::VERSION_STATUS_COMPLETE, $status);

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

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     * @expectedException \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     */
    public function testGetCurrentVersionIsEmpty()
    {
        $this->migrate->getCurrentVersion();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function testCreateVersion()
    {
        $this->migrate->createVersion();
        $records = $this->migrate->getDbDriver()->getIterator("select * from " . $this->migrationTable)->toArray();
        $this->assertEquals([
            [
                'version' => '0',
                'status' => Migration::VERSION_STATUS_UNKNOWN
            ]
        ], $records);

        // Check Bug (cannot create twice)
        $this->migrate->createVersion();
        $records = $this->migrate->getDbDriver()->getIterator("select * from " . $this->migrationTable)->toArray();
        $this->assertEquals([
            [
                'version' => '0',
                'status' => Migration::VERSION_STATUS_UNKNOWN
            ]
        ], $records);
    }
}
