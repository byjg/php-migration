<?php

namespace Tests;

use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

/**
 * @requires extension pdo_dblib
 */
class SqlServerDblibDatabaseTest extends BaseDatabase
{
    /**
     * @var Migration|null
     */
    protected ?Migration $migrate = null;

    protected string $scheme = "dblib";

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

        $uri = $this->scheme . "://sa:{$password}@{$host}/migratedatabase";

        Migration::registerDatabase(DblibDatabase::class);

        $this->migrate = new Migration(new Uri($uri), __DIR__ . '/../example/sql_server', true, $this->migrationTable);
        parent::setUp();
    }

    public function getSelectUsersVersion1()
    {
        return "select id, name, FORMAT(createdate, 'yyyy-MM-dd') as createdate from users";
    }
}
