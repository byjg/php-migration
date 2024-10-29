<?php

namespace Tests;

use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

/**
 * @requires extension pdo_dblib
 */
class SqlServerSqlsrvDatabaseTest extends SqlServerDblibDatabaseTest
{
    public function setUp(): void
    {
        $this->scheme = "sqlsrv";
        parent::setUp();
    }
}
