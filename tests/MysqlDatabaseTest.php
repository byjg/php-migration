<?php

require_once 'BaseDatabase.php';

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
        parent::setUp();
    }
}
