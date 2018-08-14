<?php

require_once 'BaseDatabase.php';

/**
 * @requires extension pdo_mysql
 */
class MysqlDatabaseTest extends BaseDatabase
{
    protected $uri = 'mysql://root:password@mysql-container/migratedatabase';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        $this->migrate = new \ByJG\DbMigration\Migration(new \ByJG\Util\Uri($this->uri), __DIR__ . '/../example/mysql');
        $this->migrate->registerDatabase("mysql", \ByJG\DbMigration\Database\MySqlDatabase::class);
        parent::setUp();
    }

    public function testUsingCustomTable()
    {
        $this->migrationTable = 'migration_table';

        $this->migrate = new \ByJG\DbMigration\Migration(new \ByJG\Util\Uri($this->uri), __DIR__ . '/../example/mysql', true, $this->migrationTable);
        $this->migrate->registerDatabase("mysql", \ByJG\DbMigration\Database\MySqlDatabase::class);

        parent::testUpVersion1();
    }
}
