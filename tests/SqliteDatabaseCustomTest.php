<?php

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Override;
use PHPUnit\Framework\TestCase;

/**
 * @requires extension pdo_sqlite
 */
#[RequiresPhpExtension('pdo_sqlite')]
class SqliteDatabaseCustomTest extends SqliteDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
