<?php

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * The SQL Server tests with a database name that is only valid quoted ("-" is not allowed in a
 * bare identifier). CREATE DATABASE, USE and DROP DATABASE used the name unquoted.
 */
#[RequiresPhpExtension('pdo_dblib')]
class SqlServerQuotedDatabaseNameTest extends SqlServerDblibDatabaseTest
{
    protected string $database = 'migrate-database';
}
