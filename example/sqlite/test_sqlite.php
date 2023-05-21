<?php

use ByJG\DbMigration\Database\SqliteDatabase;
use ByJG\DbMigration\Migration;

require __DIR__ . "/../../vendor/autoload.php";

$uri = new \ByJG\Util\Uri('sqlite:///tmp/teste.sqlite');

Migration::registerDatabase(SqliteDatabase::class);

$migration = new Migration($uri, __DIR__);

$migration->reset();

