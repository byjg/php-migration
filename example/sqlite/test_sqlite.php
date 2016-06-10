<?php

require "../../vendor/autoload.php";

$connection = new \ByJG\AnyDataset\ConnectionManagement('sqlite:///tmp/teste.sqlite');

$migration = new \ByJG\DbMigration\Migration($connection, '.');

$migration->reset();

