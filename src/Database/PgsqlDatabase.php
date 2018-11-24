<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\Factory;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class PgsqlDatabase extends AbstractDatabase
{

    public static function prepareEnvironment(UriInterface $uri)
    {
        $database = preg_replace('~^/~', '', $uri->getPath());
        $dbDriver = self::getDbDriverWithoutDatabase($uri);
        self::createDatabaseIfNotExists($dbDriver, $database);
    }

    protected static function getDbDriverWithoutDatabase(UriInterface $uri)
    {
        $customUri = new Uri($uri->__toString());
        return Factory::getDbRelationalInstance($customUri->withPath('/')->__toString());
    }

    /**
     * @param \ByJG\AnyDataset\Db\DbDriverInterface $dbDriver
     * @param $database
     */
    protected static function createDatabaseIfNotExists($dbDriver, $database)
    {
        $currentDbName = $dbDriver->getScalar(
            "SELECT datname FROM pg_catalog.pg_database WHERE lower(datname) = lower(:dbname)",
            ['dbname' => $database]
        );

        if (empty($currentDbName)) {
            $dbDriver->execute("CREATE DATABASE $database WITH encoding=\"UTF8\";");
        }
    }

    public function createDatabase()
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());
        self::createDatabaseIfNotExists($this->getDbDriver(), $database);
    }

    public function dropDatabase()
    {
        // $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());

        $iterator = $this->getDbDriver()->getIterator(
            "select 'drop table if exists \"' || tablename || '\" cascade;' command from pg_tables where schemaname = 'public';"
        );
        foreach ($iterator as $singleRow) {
            $this->getDbDriver()->execute($singleRow->get('command'));
        }
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
        $statements = explode(";", $sql);

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal($sql)
    {
        if (empty(trim($sql))) {
            return;
        }
        $this->getDbDriver()->execute($sql);
    }
}
