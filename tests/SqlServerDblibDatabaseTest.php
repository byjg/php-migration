<?php

use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

require_once 'BaseDatabase.php';

/**
 * @requires extension pdo_dblib
 */
class SqlServerDblibDatabaseTest extends BaseDatabase
{
    /**
     * @var Migration
     */
    protected $migrate = null;

    protected $scheme = "dblib";

    public function setUp(): void
    {
        $host = getenv('MSSQL_TEST_HOST');
        if (empty($host)) {
            $host = "127.0.0.1";
        }
        $password = getenv('MSSQL_PASSWORD');
        if (empty($password)) {
            $password = 'Pa55word';
        }

        $uri = $this->scheme . "://sa:${password}@${host}/migratedatabase";

        $this->migrate = new Migration(new Uri($uri), __DIR__ . '/../example/sql_server', true, $this->migrationTable);
        $this->migrate->registerDatabase($this->scheme, DblibDatabase::class);
        parent::setUp();
    }
}
