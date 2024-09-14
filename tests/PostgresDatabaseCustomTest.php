<?php

namespace Tests;

/**
 * @requires extension pdo_pgsql
 */
class PostgresDatabaseCustomTest extends PostgresDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
