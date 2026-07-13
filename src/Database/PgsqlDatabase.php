<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class PgsqlDatabase extends AbstractDatabase
{
    #[\Override]
    public static function schema(): string
    {
        return 'pgsql';
    }

    #[\Override]
    public static function prepareEnvironment(UriInterface|Uri $uri): void
    {
        $uriInstance = $uri instanceof Uri ? $uri : new Uri($uri->__toString());
        $database = static::getDatabaseName($uriInstance);
        $executor = static::getExecutorWithoutDatabase($uri, 'postgres');
        static::createDatabaseIfNotExists($executor, $database);
    }

    /**
     * @param DatabaseExecutor $executor
     * @param $database
     */
    protected static function createDatabaseIfNotExists(DatabaseExecutor $executor, string $database): void
    {
        $currentDbName = $executor->getScalar(
            "SELECT datname FROM pg_catalog.pg_database WHERE lower(datname) = lower(:dbname)",
            ['dbname' => $database]
        );

        if (empty($currentDbName)) {
            $executor->execute("CREATE DATABASE $database WITH encoding=\"UTF8\";");
        }
    }

    #[\Override]
    public function createDatabase(): void
    {
        $database = static::getDatabaseName($this->getDbDriver()->getUri());
        static::createDatabaseIfNotExists($this->getExecutor(), $database);
    }

    #[\Override]
    public function dropDatabase(): void
    {
        $iterator = $this->getExecutor()->getIterator(
            "select 'drop table if exists \"' || tablename || '\" cascade;' command from pg_tables where schemaname = 'public';"
        );
        foreach ($iterator as $singleRow) {
            $this->getExecutor()->execute($singleRow->get('command'));
        }
    }

    /**
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    #[\Override]
    public function createVersion(): void
    {
        $this->getExecutor()->execute('CREATE TABLE IF NOT EXISTS ' . $this->getMigrationTable() . ' (version int, status varchar(20), PRIMARY KEY (version))');
        $this->checkExistsVersion();
    }

    #[\Override]
    public function executeSql(string $sql): void
    {
        $statements = preg_split("/;(\r\n|\r|\n)/", $sql);

        if ($statements === false) {
            $statements = [$sql];
        }

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal(string $sql): void
    {
        if (empty(trim($sql))) {
            return;
        }
        $this->getExecutor()->execute($sql);
    }

    #[\Override]
    public function isDatabaseVersioned(): bool
    {
        return $this->isTableExists('public', $this->getMigrationTable());
    }
}
