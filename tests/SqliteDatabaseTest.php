<?php

require_once 'BaseDatabase.php';

/**
 * @requires extension pdo_sqlite
 */
class SqliteDatabaseTest extends BaseDatabase
{
    protected $path = __DIR__ . '/../example/sqlite/test.sqlite';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        # Dump SQLite database.
        file_put_contents($this->path, '');

        $uri = new \ByJG\Util\Uri("sqlite://{$this->path}");
        $this->migrate = new \ByJG\DbMigration\Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);
        $this->migrate->registerDatabase("sqlite", \ByJG\DbMigration\Database\SqliteDatabase::class);
        parent::setUp();
    }

    public function testUsingCustomTable()
    {
        $this->migrationTable = 'migration_table';

        file_put_contents($this->path, '');

        $uri = new \ByJG\Util\Uri("sqlite://{$this->path}");
        $this->migrate = new \ByJG\DbMigration\Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);
        $this->migrate->registerDatabase("sqlite", \ByJG\DbMigration\Database\SqliteDatabase::class);

        parent::testUpVersion1();
    }

}
