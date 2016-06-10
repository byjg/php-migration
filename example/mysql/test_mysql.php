<?php

require "../../vendor/autoload.php";

/**
 * Make sure you have a database with the user 'migrateuser' and password 'migratepwd'
 * 
 * This user need to have grant for DDL commands; 
 */

$connection = new \ByJG\AnyDataset\ConnectionManagement('mysql://migrateuser:migratepwd@localhost/migratedatabase');

$migration = new \ByJG\DbMigration\Migration($connection, '.');

$migration->prepareEnvironment();

$migration->reset();

