<?php

require_once 'SqlServerSqlsrvDatabaseTest.php';

/**
 * @requires extension pdo_dblib
 */
class SqlServerSqlsrvDatabaseCustomTest extends SqlServerSqlsrvDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
