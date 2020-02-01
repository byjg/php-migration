<?php

require_once 'BaseDatabase.php';

/**
 * @requires extension pdo_sqlite
 */
class SqliteDatabaseTest extends BaseDatabase
{
    protected $path = ':memory:';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        # Dump SQLite database.
        $this->prepareDatabase();

        $uri = new \ByJG\Util\Uri("sqlite://{$this->path}");
        $this->migrate = new \ByJG\DbMigration\Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);
        $this->migrate->registerDatabase("sqlite", \ByJG\DbMigration\Database\SqliteDatabase::class);
        parent::setUp();
    }

    public function testUsingCustomTable()
    {
        $this->migrationTable = 'migration_table';

        $this->prepareDatabase();

        $uri = new \ByJG\Util\Uri("sqlite://{$this->path}");
        $this->migrate = new \ByJG\DbMigration\Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);
        $this->migrate->registerDatabase("sqlite", \ByJG\DbMigration\Database\SqliteDatabase::class);

        parent::testUpVersion1();
    }

    protected function prepareDatabase() {
        if ($this->path != ":memory:") {
            file_put_contents($this->path, '');
        }
    }
}
