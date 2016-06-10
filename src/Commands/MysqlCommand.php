<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\AnyDataset\Repository\DBDataset;

class MySqlCommand extends AbstractCommand
{

    public static function prepareEnvironment(ConnectionManagement $connection)
    {
        $database = $connection->getDatabase();

        $newConnection = new ConnectionManagement(str_replace("/$database", "/", $connection->getDbConnectionString()));
        $dbDataset = new DBDataset($newConnection->getDbConnectionString());
        $dbDataset->execSQL("CREATE SCHEMA IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 ;");
    }

    public function createDatabase()
    {
        $database = $this->getDbDataset()->getConnectionManagement()->getDatabase();

        $this->getDbDataset()->execSQL("CREATE SCHEMA IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 ;");
        $this->getDbDataset()->execSQL("USE `$database`");
    }

    public function dropDatabase()
    {
        $database = $this->getDbDataset()->getConnectionManagement()->getDatabase();

        $this->getDbDataset()->execSQL("drop database `$database`");
    }

    public function createVersion()
    {
        $this->getDbDataset()->execSQL('CREATE TABLE IF NOT EXISTS migration_version (version int)');
        $this->checkExistsVersion();
    }

    public function executeSql($sql)
    {
        $this->getDbDataset()->execSQL($sql);
    }
}
