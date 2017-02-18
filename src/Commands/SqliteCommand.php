<?php

namespace ByJG\DbMigration\Commands;

use ByJG\Util\Uri;

class SqliteCommand extends AbstractCommand
{

    public static function prepareEnvironment(Uri $uri)
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
            $this->getDbDriver()->execute($row->getField('command'));
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
        $this->getDbDriver()->execute($sql);
    }
}
