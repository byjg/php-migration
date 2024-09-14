<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class PgsqlDatabase extends AbstractDatabase
{
    public static function schema(): string
    {
        return 'pgsql';
    }

    public static function prepareEnvironment(UriInterface $uri): void
    {
        $database = preg_replace('~^/~', '', $uri->getPath());
        $dbDriver = static::getDbDriverWithoutDatabase($uri);
        static::createDatabaseIfNotExists($dbDriver, $database);
    }

    protected static function getDbDriverWithoutDatabase(UriInterface $uri): DbDriverInterface
    {
        $customUri = new Uri($uri->__toString());
        return Factory::getDbInstance($customUri->withPath('/postgres')->__toString());
    }

    /**
     * @param DbDriverInterface $dbDriver
     * @param $database
     */
    protected static function createDatabaseIfNotExists(DbDriverInterface $dbDriver, string $database): void
    {
        $currentDbName = $dbDriver->getScalar(
            "SELECT datname FROM pg_catalog.pg_database WHERE lower(datname) = lower(:dbname)",
            ['dbname' => $database]
        );

        if (empty($currentDbName)) {
            $dbDriver->execute("CREATE DATABASE $database WITH encoding=\"UTF8\";");
        }
    }

    public function createDatabase(): void
    {
        $database = preg_replace('~^/~', '', $this->getDbDriver()->getUri()->getPath());
        static::createDatabaseIfNotExists($this->getDbDriver(), $database);
    }

    public function dropDatabase(): void
    {
        $iterator = $this->getDbDriver()->getIterator(
            "select 'drop table if exists \"' || tablename || '\" cascade;' command from pg_tables where schemaname = 'public';"
        );
        foreach ($iterator as $singleRow) {
            $this->getDbDriver()->execute($singleRow->get('command'));
        }
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
        $statements = preg_split("/;(\r\n|\r|\n)/", $sql);

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal(string $sql): void
    {
        if (empty(trim($sql))) {
            return;
        }
        $this->getDbDriver()->execute($sql);
    }

    public function isDatabaseVersioned(): bool
    {
        return $this->isTableExists('public', $this->getMigrationTable());
    }
}
