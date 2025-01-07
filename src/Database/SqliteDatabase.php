<?php

namespace ByJG\DbMigration\Database;

use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use Psr\Http\Message\UriInterface;

class SqliteDatabase extends AbstractDatabase
{
    public static function schema(): string
    {
        return 'sqlite';
    }

    public static function prepareEnvironment(UriInterface $uri): void
    {
        // There is no need to prepare the database
    }

    public function createDatabase(): void
    {
        // There is no need to create a database in SQLite
    }

    public function dropDatabase(): void
    {
        $iterator = $this->getDbDriver()->getIterator("
            select
                'drop ' || type || ' ' || name || ';' as command
            from sqlite_master
            where name <> 'sqlite_sequence' and name not like 'sqlite_autoindex_%'
            order by CASE type
                         WHEN 'index' THEN 0
                         WHEN 'trigger' THEN 1
                         WHEN 'view' THEN 2
                         ELSE 99
                    END;
        ");

        $list = $iterator->toArray();

        foreach ($list as $row) {
            $this->getDbDriver()->execute($row['command']);
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

    protected function isTableExists(?string $schema, string $table): bool
    {
        $count = $this->getDbDriver()->getScalar(
            "SELECT count(*) FROM sqlite_master WHERE type='table' AND name=:table",
            [
                "table" => $table
            ]
        );

        return (intval($count) !== 0);
    }

    public function isDatabaseVersioned(): bool
    {
        return $this->isTableExists(null, $this->getMigrationTable());
    }
}
