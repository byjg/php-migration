<?php

require_once 'SqlServerDblibDatabaseTest.php';

/**
 * @requires extension pdo_dblib
 */
class SqlServerDblibDatabaseCustomTest extends SqlServerDblibDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
