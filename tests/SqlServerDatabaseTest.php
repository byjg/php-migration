<?php

require_once 'BaseDatabase.php';

class SqlServerDatabaseTest extends BaseDatabase
{
    protected $uri = 'dblib://sa:Pa$$word!@mssql-container/migratedatabase';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        $this->migrate = new \ByJG\DbMigration\Migration(new \ByJG\Util\Uri($this->uri), __DIR__ . '/../example/sql_server');
        parent::setUp();
    }
}
