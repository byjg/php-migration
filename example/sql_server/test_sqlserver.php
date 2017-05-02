<?php

require __DIR__ . "/../../vendor/autoload.php";

/**
 * Make sure you have a database with the user 'migrateuser' and password 'migratepwd'
 * 
 * This user need to have grant for DDL commands; 
 */

$uri = new \ByJG\Util\Uri('dblib://sa:Pa$$word!@mssql-container/migratedatabase');

$migration = new \ByJG\DbMigration\Migration($uri, __DIR__);

$migration->prepareEnvironment();

$migration->reset();

