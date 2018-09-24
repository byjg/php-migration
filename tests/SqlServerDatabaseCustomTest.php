<?php

require_once 'SqlServerDatabaseTest.php';

/**
 * @requires extension pdo_dblib
 */
class SqlServerDatabaseCustomTest extends SqlServerDatabaseTest
{
    protected $migrationTable = "some_table_version";
}
