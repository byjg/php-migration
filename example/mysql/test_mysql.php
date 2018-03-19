<?php

require __DIR__ . "/../../vendor/autoload.php";

/**
 * Make sure you have a database with the user 'migrateuser' and password 'migratepwd'
 * 
 * This user need to have grant for DDL commands; 
 */

$uri = new \ByJG\Util\Uri('mysql://root:password@mysql-container/migratedatabase');

$migration = new \ByJG\DbMigration\Migration($uri, __DIR__);
$migration->registerDatabase('mysql', \ByJG\DbMigration\Database\MySqlDatabase::class);

$migration->prepareEnvironment();

$migration->reset();

