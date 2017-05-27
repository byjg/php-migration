<?php

require_once 'BaseDatabase.php';

class SqliteDatabaseTest extends BaseDatabase
{
    protected $uri = 'sqlite:///tmp/teste.sqlite';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        $this->migrate = new \ByJG\DbMigration\Migration(new \ByJG\Util\Uri($this->uri), __DIR__ . '/../example/sqlite');
        parent::setUp();
    }
}
