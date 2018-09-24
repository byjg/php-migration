<?php

require_once 'PostgresDatabaseTest.php';

/**
 * @requires extension pdo_pgsql
 */
class PostgresDatabaseCustomTest extends PostgresDatabaseTest
{
    protected $migrationTable = "some_table_version";
}
