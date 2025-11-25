<?php

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Override;

/**
 * @requires extension pdo_dblib
 */
#[RequiresPhpExtension('pdo_dblib')]
class SqlServerSqlsrvDatabaseCustomTest extends SqlServerSqlsrvDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
