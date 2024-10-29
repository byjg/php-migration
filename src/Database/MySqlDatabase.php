<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class MySqlDatabase extends AbstractDatabase
{
    public static function schema(): array
    {
        return ['mysql', 'mariadb'];
    }

    public static function prepareEnvironment(UriInterface $uri): void
    {
        $database = preg_replace('~^/~', '', $uri->getPath());

        $customUri = new Uri($uri->__toString());

        $dbDriver = Factory::getDbInstance($customUri->withPath('/')->__toString());
        $dbDriver->execute("CREATE SCHEMA IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 ;");
    }

    public function createDatabase(): void
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("CREATE SCHEMA IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 ;");
        $this->getDbDriver()->execute("USE `$database`");
    }

    public function dropDatabase(): void
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $this->getDbDriver()->execute("drop database `$database`");
    }

    /**
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    public function createVersion(): void
    {
        $this->getDbDriver()->execute('CREATE TABLE IF NOT EXISTS ' . $this->getMigrationTable() . ' (version int, status varchar(20), PRIMARY KEY (version))');
        $this->checkExistsVersion();
    }

    public function executeSql(string $sql): void
    {
        $this->getDbDriver()->execute($sql);
    }

    public function supportsTransaction(): bool
    {
        // MySQL doesn't support transaction for DDL commands
        // https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html
        return false;
    }
}
