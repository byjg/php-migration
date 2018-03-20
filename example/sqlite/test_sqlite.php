<?php

require __DIR__ . "/../../vendor/autoload.php";

$uri = new \ByJG\Util\Uri('sqlite:///tmp/teste.sqlite');

$migration = new \ByJG\DbMigration\Migration($uri, __DIR__);
$migration->registerDatabase('sqlite', \ByJG\DbMigration\Database\SqliteDatabase::class);

$migration->reset();

