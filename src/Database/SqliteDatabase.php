<?php

namespace ByJG\DbMigration\Database;

use Psr\Http\Message\UriInterface;

class SqliteDatabase extends AbstractDatabase
{

    public static function prepareEnvironment(UriInterface $uri)
    {
    }

    public function createDatabase()
    {
    }

    public function dropDatabase()
    {
        $iterator = $this->getDbDriver()->getIterator("
            select 
                'drop ' || type || ' ' || name || ';' as command 
            from sqlite_master 
            where name <> 'sqlite_sequence'
            order by CASE type
                         WHEN 'index' THEN 0
                         WHEN 'trigger' THEN 1
                         WHEN 'view' THEN 2
                         ELSE 99
                    END;
        ");

        foreach ($iterator as $row) {
            $this->getDbDriver()->execute($row->get('command'));
        }
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    public function createVersion()
    {
        $this->getDbDriver()->execute('CREATE TABLE IF NOT EXISTS ' . $this->getMigrationTable() . ' (version int, status varchar(20))');
        $this->checkExistsVersion();
    }

    public function executeSql($sql)
    {
        $statements = preg_split("/;(\r\n|\r|\n)/", $sql);

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal($sql)
    {
        $this->getDbDriver()->execute($sql);
    }

    protected function isTableExists($schema, $table)
    {
        $count = $this->getDbDriver()->getScalar(
            "SELECT count(*) FROM sqlite_master WHERE type='table' AND name=[[table]]",
            [
                "table" => $table
            ]
        );

        return (intval($count) !== 0);
    }

    public function isDatabaseVersioned()
    {
        return $this->isTableExists(null, $this->getMigrationTable());
    }
}
