<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class DblibDatabase extends AbstractDatabase
{
    #[\Override]
    public static function schema(): array
    {
        return ['dblib', 'sqlsrv'];
    }

    #[\Override]
    protected static function getDatabaseName(Uri $uri): string
    {
        return $uri->getQueryPart('dbname') ?? $uri->getQueryPart('Database') ?? ltrim($uri->getPath(), '/');
    }

    #[\Override]
    public static function prepareEnvironment(UriInterface|Uri $uri): void
    {
        $database = static::getDatabaseName($uri);
        $dbDriver = static::getDbDriverWithoutDatabase($uri);
        $dbDriver->execute("IF NOT EXISTS(select * from sys.databases where name='$database') CREATE DATABASE $database");
    }

    #[\Override]
    public function createDatabase(): void
    {
        $database = static::getDatabaseName($this->getDbDriver()->getUri());

        $this->getDbDriver()->execute("IF NOT EXISTS(select * from sys.databases where name='$database') CREATE DATABASE $database");
        $this->getDbDriver()->execute("USE $database");
    }

    #[\Override]
    public function dropDatabase(): void
    {
        $database = static::getDatabaseName($this->getDbDriver()->getUri());

        $this->getDbDriver()->execute("use master");
        $this->getDbDriver()->execute("drop database $database");
    }

    protected function createTableIfNotExists(string $database, string $createTable): void
    {
        $this->getDbDriver()->execute("use $database");

        $sql = "IF (NOT EXISTS (SELECT *
                 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = 'dbo'
                 AND  TABLE_NAME = '" . $this->getMigrationTable() . "'))
            BEGIN
                $createTable
            END";

        $this->getDbDriver()->execute($sql);
    }

    /**
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    #[\Override]
    public function createVersion(): void
    {
        $database = static::getDatabaseName($this->getDbDriver()->getUri());

        $createTable = 'CREATE TABLE ' . $this->getMigrationTable() . ' (version int, status varchar(20), PRIMARY KEY (version))';
        $this->createTableIfNotExists($database, $createTable);
        $this->checkExistsVersion();
    }

    #[\Override]
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

    /**
     * @param string|null $schema
     * @param string $table
     * @return bool
     */
    #[\Override]
    protected function isTableExists(?string $schema, string $table): bool
    {
        $count = $this->getDbDriver()->getScalar(
            'SELECT count(*) FROM information_schema.tables ' .
            ' WHERE table_catalog = :schema ' .
            '  AND table_name = :table ',
            [
                "schema" => $schema,
                "table" => $table
            ]
        );

        return (intval($count) !== 0);
    }
}
