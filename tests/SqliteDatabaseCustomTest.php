<?php

namespace Tests;

/**
 * @requires extension pdo_sqlite
 */
class SqliteDatabaseCustomTest extends SqliteDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
