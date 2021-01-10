<?php

use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

require_once 'SqlServerDblibDatabaseTest.php';

/**
 * @requires extension pdo_dblib
 */
class SqlServerSqlsrvDatabaseTest extends SqlServerDblibDatabaseTest
{
    public function setUp()
    {
        $this->scheme = "sqlsrv";
        parent::setUp();
    }
}
