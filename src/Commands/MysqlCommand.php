<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\Factory;
use ByJG\Util\Uri;

class MySqlCommand extends AbstractCommand
{

    public static function prepareEnvironment(Uri $uri)
    {
        $database = preg_replace('~^/~', '', $uri->getPath());

        $customUri = new Uri($uri->__toString());

        $dbDriver = Factory::getDbRelationalInstance($customUri->withPath('/')->__toString());
        $dbDriver->execute("CREATE SCHEMA IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 ;");
    }

    public function createDatabase()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("CREATE SCHEMA IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 ;");
        $this->getDbDriver()->execute("USE `$database`");
    }

    public function dropDatabase()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("drop database `$database`");
    }

    public function createVersion()
    {
        $this->getDbDriver()->execute('CREATE TABLE IF NOT EXISTS migration_version (version int)');
        $this->checkExistsVersion();
    }

    public function executeSql($sql)
    {
        $this->getDbDriver()->execute($sql);
    }
}
