<?php

require_once 'PostgresDatabaseTest.php';

/**
 * @requires extension pdo_pgsql
 */
class PostgresDatabaseCustomTest extends PostgresDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
