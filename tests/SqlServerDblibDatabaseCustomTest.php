<?php

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Override;

/**
 * @requires extension pdo_dblib
 */
#[RequiresPhpExtension('pdo_dblib')]
class SqlServerDblibDatabaseCustomTest extends SqlServerDblibDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
