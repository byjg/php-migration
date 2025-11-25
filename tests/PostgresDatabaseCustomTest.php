<?php

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Override;

#[RequiresPhpExtension('pdo_pgsql')]
class PostgresDatabaseCustomTest extends PostgresDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
