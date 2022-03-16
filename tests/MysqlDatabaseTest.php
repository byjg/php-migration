<?php

use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

require_once 'BaseDatabase.php';

/**
 * @requires extension pdo_mysql
 */
class MysqlDatabaseTest extends BaseDatabase
{
    /**
     * @var Migration
     */
    protected $migrate = null;

    public function setUp(): void
    {
        $host = getenv('MYSQL_TEST_HOST');
        if (empty($host)) {
            $host = "127.0.0.1";
        }
        $password = getenv('MYSQL_PASSWORD');
        if (empty($password)) {
            $password = 'password';
        }
        if ($password == '.') {
            $password = "";
        }

        $uri = "mysql://root:${password}@${host}/migratedatabase";

        $this->migrate = new Migration(new Uri($uri), __DIR__ . '/../example/mysql', true, $this->migrationTable);
        $this->migrate->registerDatabase("mysql", MySqlDatabase::class);
        parent::setUp();
    }
}
