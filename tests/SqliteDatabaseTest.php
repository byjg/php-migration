<?php

namespace Tests;

use ByJG\DbMigration\Database\SqliteDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Override;

/**
 * @requires extension pdo_sqlite
 */
#[RequiresPhpExtension('pdo_sqlite')]
class SqliteDatabaseTest extends BaseDatabase
{
    protected $path = '';

    /**
     * @var Migration
     */
    protected ?Migration $migrate = null;

    #[\Override]
    public function setUp(): void
    {
        $this->path = getenv('SQLITE_TEST_HOST');
        if (empty($this->path)) {
            $this->path = ':memory:';
        }

        # Dump SQLite database.
        $this->prepareDatabase();

        $uri = new Uri("sqlite://{$this->path}");

        Migration::registerDatabase(SqliteDatabase::class);

        $this->migrate = new Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);
        parent::setUp();
    }

    public function testUsingCustomTable(): void
    {
        $this->migrationTable = 'migration_table';

        $this->prepareDatabase();

        Migration::registerDatabase(SqliteDatabase::class);

        $uri = new Uri("sqlite://{$this->path}");
        $this->migrate = new Migration($uri, __DIR__ . '/../example/sqlite', true, $this->migrationTable);

        parent::testUpVersion1();
    }

    protected function prepareDatabase(): void {
        if ($this->path != ":memory:") {
            file_put_contents($this->path, '');
        }
    }
}
