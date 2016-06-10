<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\ConnectionManagement;

class SqliteCommand extends AbstractCommand
{

    public static function prepareEnvironment(ConnectionManagement $connection)
    {
    }

    public function createDatabase()
    {
    }

    public function dropDatabase()
    {
        $iterator = $this->getDbDataset()->getIterator("
            select 
                'drop table ' || name || ';' as command 
            from sqlite_master 
            where type = 'table' 
              and name <> 'sqlite_sequence';
        ");

        foreach ($iterator as $row) {
            $this->getDbDataset()->execSQL($row->getField('command'));
        }
    }

    public function createVersion()
    {
        $this->getDbDataset()->execSQL('CREATE TABLE IF NOT EXISTS migration_version (version int)');
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
        $this->getDbDataset()->execSQL($sql);
    }
}
