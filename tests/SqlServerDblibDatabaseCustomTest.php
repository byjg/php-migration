<?php

namespace Tests;

/**
 * @requires extension pdo_dblib
 */
class SqlServerDblibDatabaseCustomTest extends SqlServerDblibDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
