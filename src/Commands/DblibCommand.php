<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\Factory;
use ByJG\Util\Uri;

class DblibCommand extends AbstractCommand
{

    public static function prepareEnvironment(Uri $uri)
    {
        $database = preg_replace('~^/~', '', $uri->getPath());

        $customUri = new Uri($uri->__toString());

        $dbDriver = Factory::getDbRelationalInstance($customUri->withPath('/')->__toString());
        $dbDriver->execute("IF NOT EXISTS(select * from sys.databases where name='$database') CREATE DATABASE $database");
    }

    public function createDatabase()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("IF NOT EXISTS(select * from sys.databases where name='$database') CREATE DATABASE $database");
        $this->getDbDriver()->execute("USE $database");
    }

    public function dropDatabase()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("use master");
        $this->getDbDriver()->execute("drop database $database");
    }

    protected function createTableIfNotExists($database, $table, $createTable)
    {
        $this->getDbDriver()->execute("use $database");

        $sql = "IF (NOT EXISTS (SELECT * 
                 FROM INFORMATION_SCHEMA.TABLES 
                 WHERE TABLE_SCHEMA = 'dbo' 
                 AND  TABLE_NAME = '$table'))
            BEGIN
                $createTable
            END";

        $this->getDbDriver()->execute($sql);
    }

    public function createVersion()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());
        $table = 'migration_version';
        $createTable = 'CREATE TABLE migration_version (version int, status varchar(20))';
        $this->createTableIfNotExists($database, $table, $createTable);
        $this->checkExistsVersion();
    }

    public function executeSql($sql)
    {
        $statements = explode(";", $sql);

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal($sql)
    {
        $this->getDbDriver()->execute($sql);
    }
}
