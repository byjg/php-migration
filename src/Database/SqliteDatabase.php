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
                'drop table ' || name || ';' as command 
            from sqlite_master 
            where type = 'table' 
              and name <> 'sqlite_sequence';
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
        $statements = explode(";", $sql);

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal($sql)
    {
        $this->getDbDriver()->execute($sql);
    }
}
