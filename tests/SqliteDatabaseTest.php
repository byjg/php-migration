<?php

use ByJG\DbMigration\Database\SqliteDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

require_once 'BaseDatabase.php';

/**
 * @requires extension pdo_sqlite
 */
class SqliteDatabaseTest extends BaseDatabase
{
    protected $path = '';

    /**
     * @var Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        $this->path = getenv('SQLITE_TEST_HOST');
        if (empty($this->path)) {
            $this->path = ':memory:';
        }

        # Dump SQLite database.
        $this->prepareDatabase();

        $uri = new Uri("sqlite://{$this->path}");

        $this->migrate = new Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);
        $this->migrate->registerDatabase("sqlite", SqliteDatabase::class);
        parent::setUp();
    }

    public function testUsingCustomTable()
    {
        $this->migrationTable = 'migration_table';

        $this->prepareDatabase();

        $uri = new Uri("sqlite://{$this->path}");
        $this->migrate = new Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);
        $this->migrate->registerDatabase("sqlite", SqliteDatabase::class);

        parent::testUpVersion1();
    }

    protected function prepareDatabase() {
        if ($this->path != ":memory:") {
            file_put_contents($this->path, '');
        }
    }
}
