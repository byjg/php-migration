<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\Factory;
use ByJG\Util\Uri;

class PgsqlCommand extends AbstractCommand
{

    public static function prepareEnvironment(Uri $uri)
    {
        $database = preg_replace('~^/~', '', $uri->getPath());
        $dbDriver = self::getDbDriverWithoutDatabase($uri);
        self::createDatabaseIfNotExists($dbDriver, $database);
    }

    protected static function getDbDriverWithoutDatabase(Uri $uri)
    {
        $customUri = new Uri($uri->__toString());
        return Factory::getDbRelationalInstance($customUri->withPath('/')->__toString());
    }

    protected static function createDatabaseIfNotExists($dbDriver, $database)
    {
        $currentDbName = $dbDriver->getScalar(
            "SELECT datname FROM pg_catalog.pg_database WHERE lower(datname) = lower(:dbname)",
            ['dbname' => $database]
        );

        if (empty($currentDbName)) {
            $dbDriver->execute("CREATE DATABASE $database WITH encoding=UTF8;");
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
            $this->getDbDriver()->execute($singleRow->getField('command'));
        }
    }

    public function createVersion()
    {
        $this->getDbDriver()->execute('CREATE TABLE IF NOT EXISTS migration_version (version int)');
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
