<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\Factory;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class MySqlDatabase extends AbstractDatabase
{

    public static function prepareEnvironment(UriInterface $uri)
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

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    public function createVersion()
    {
        $this->getDbDriver()->execute('CREATE TABLE IF NOT EXISTS migration_version (version int, status varchar(20))');
        $this->checkExistsVersion();
    }

    public function executeSql($sql)
    {
        $this->getDbDriver()->execute($sql);
    }
}
