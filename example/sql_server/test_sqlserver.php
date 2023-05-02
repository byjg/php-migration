<?php

use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;

require __DIR__ . "/../../vendor/autoload.php";

/**
 * Make sure you have a database with the user 'migrateuser' and password 'migratepwd'
 * 
 * This user need to have grant for DDL commands; 
 */

$uri = new \ByJG\Util\Uri('dblib://sa:Pa55word@mssql-container/migratedatabase');

Migration::registerDatabase(DblibDatabase::class);

$migration = new Migration($uri, __DIR__);

$migration->prepareEnvironment();

$migration->reset();

