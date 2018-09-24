<?php

require_once 'BaseDatabase.php';


/**
 * @requires extension pdo_pgsql
 */
class PostgresDatabaseTest extends BaseDatabase
{
    protected $uri = 'pgsql://postgres:password@postgres-container/migratedatabase';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        $this->migrate = new \ByJG\DbMigration\Migration(new \ByJG\Util\Uri($this->uri), __DIR__ . '/../example/postgres', true, $this->migrationTable);
        $this->migrate->registerDatabase("pgsql", \ByJG\DbMigration\Database\PgsqlDatabase::class);
        parent::setUp();
    }
}
