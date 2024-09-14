<?php

use ByJG\DbMigration\Exception\DatabaseDoesNotRegistered;
use ByJG\DbMigration\Exception\DatabaseIsIncompleteException;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\DbMigration\Migration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

abstract class BaseDatabase extends TestCase
{
    protected UriInterface|null $uri = null;

    /**
     * @var Migration
     */
    protected ?Migration $migrate = null;

    protected string $migrationTable = "migration_version";

    /**
     * @throws DatabaseDoesNotRegistered
     */
    public function setUp(): void
    {
        // create Migrate object in the parent!!!

        $this->migrate->prepareEnvironment();
    }

    /**
     * @throws DatabaseDoesNotRegistered
     */
    public function tearDown(): void
    {
        $this->migrate->getDbCommand()->dropDatabase();
    }

    /**
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function testVersion0()
    {
        $this->migrate->reset(0);
        $this->assertVersion0();
    }

    public function testIsDatabaseVersioned()
    {
        $this->assertFalse($this->migrate->isDatabaseVersioned());
        $this->migrate->reset();
        $this->assertTrue($this->migrate->isDatabaseVersioned());
    }

    /**
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
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
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
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
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
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
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function testDownVersion0()
    {
        $this->migrate->reset();
        $this->assertVersion2();
        $this->migrate->down(0);
        $this->assertVersion0();
    }

    protected function getExpectedUsersVersion0(): array
    {
        return [
            ["id" => 1, "name" => 'John Doe', 'createdate' => '20160110'],
            ["id" => 2, "name" => 'Jane Doe', 'createdate' => '20151230']
        ];
    }

    protected function getExpectedUsersVersion1(): array
    {
        return [
            ["id" => 1, "name" => 'John Doe', 'createdate' => '2016-01-10'],
            ["id" => 2, "name" => 'Jane Doe', 'createdate' => '2015-12-30']
        ];
    }

    protected function getExpectedPostsVersion2(): array
    {
        return [
            ["id" => 1, "userid" => 1, "title" => 'Testing', 'post' => "<!-- wp:paragraph -->\\n<p>This is an example page. It's different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>\\n<!-- /wp:paragraph -->\\n\\n<!-- wp:quote -->\\n<blockquote class=\"wp-block-quote\"><p>Hi there! I'm a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin' caught in the rain.)</p></blockquote>\\n<!-- /wp:quote -->\\n\\n<!-- wp:paragraph -->\\n<p>...or something like this:</p>\\n<!-- /wp:paragraph -->\\n\\n<!-- wp:quote -->\\n<blockquote class=\"wp-block-quote\"><p>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</p></blockquote>\\n<!-- /wp:quote -->\\n\\n<!-- wp:paragraph -->\\n<p>As a new WordPress user, you should go to <a href=\"http://home.home.lcl/wordpress/wp-admin/\">your dashboard</a> to delete this page and create new pages for your content. Have fun!</p>\\n<!-- /wp:paragraph -->"],
        ];
    }

    /**
     * @throws DatabaseDoesNotRegistered
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    protected function assertVersion0(): void
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
     * @throws DatabaseDoesNotRegistered
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    protected function assertVersion1(): void
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
     * @throws DatabaseDoesNotRegistered
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    protected function assertVersion2()
    {
        $version = $this->migrate->getDbDriver()->getScalar('select version from '. $this->migrationTable);
        $this->assertEquals(2, $version);
        $status = $this->migrate->getDbDriver()->getScalar('select status from '. $this->migrationTable);
        $this->assertEquals(Migration::VERSION_STATUS_COMPLETE, $status);

        // Users
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

        // Posts
        $iterator = $this->migrate->getDbDriver()->getIterator('select * from posts');

        $this->assertTrue($iterator->hasNext());
        $row = $iterator->moveNext();
        $this->assertEquals(
            $this->getExpectedPostsVersion2()[0],
            $row->toArray()
        );
    }

    /**
     * @throws DatabaseDoesNotRegistered
     * @throws OldVersionSchemaException
     */
    public function testGetCurrentVersionIsEmpty()
    {
        $this->expectException(DatabaseNotVersionedException::class);
        $this->migrate->getCurrentVersion();
    }

    /**
     * @throws DatabaseDoesNotRegistered
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
