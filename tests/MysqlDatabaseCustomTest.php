<?php

require_once 'MysqlDatabaseTest.php';

/**
 * @requires extension pdo_mysql
 */
class MysqlDatabaseCustomTest extends MysqlDatabaseTest
{
    protected string $migrationTable = "some_table_version";
}
