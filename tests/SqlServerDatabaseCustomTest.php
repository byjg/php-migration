<?php

require_once 'SqlServerDblibDatabaseTest.php';

/**
 * @requires extension pdo_dblib
 */
class SqlServerDatabaseCustomTest extends SqlServerDblibDatabaseTest
{
    protected $migrationTable = "some_table_version";
}
