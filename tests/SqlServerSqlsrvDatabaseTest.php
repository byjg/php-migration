<?php

namespace Tests;

use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Override;

/**
 * @requires extension pdo_dblib
 */
#[RequiresPhpExtension('pdo_dblib')]
class SqlServerSqlsrvDatabaseTest extends SqlServerDblibDatabaseTest
{
    #[\Override]
    public function setUp(): void
    {
        if (!extension_loaded('pdo_sqlsrv')) {
            $this->skipTest = "pdo_sqlsrv extension not loaded";
            return;
        }
        $this->scheme = "sqlsrv";
        parent::setUp();
    }
}
