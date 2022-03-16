<?php

use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

require_once 'BaseDatabase.php';

/**
 * @requires extension pdo_dblib
 */
class SqlServerDatabaseTest extends BaseDatabase
{
    /**
     * @var Migration
     */
    protected $migrate = null;

    public function getExpectedUsersVersion1()
    {
        return [
            ["id" => 1, "name" => 'John Doe', 'createdate' => 'Jan 10 2016 12:00:00:AM'],
            ["id" => 2, "name" => 'Jane Doe', 'createdate' => 'Dec 30 2015 12:00:00:AM']
        ];
    }

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

        $uri = "dblib://sa:${password}@${host}/migratedatabase";

        $this->migrate = new Migration(new Uri($uri), __DIR__ . '/../example/sql_server', true, $this->migrationTable);
        $this->migrate->registerDatabase("dblib", DblibDatabase::class);
        parent::setUp();
    }
}
