<?php

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('pdo_mysql')]
class MysqlDatabaseCustomTest extends MysqlDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
