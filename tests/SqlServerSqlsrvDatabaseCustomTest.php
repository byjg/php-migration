<?php

namespace Tests;

/**
 * @requires extension pdo_dblib
 */
class SqlServerSqlsrvDatabaseCustomTest extends SqlServerSqlsrvDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
