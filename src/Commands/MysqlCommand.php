<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\Repository\DBDataset;

class MySqlCommand implements CommandInterface
{
    /**
     * @var DBDataset
     */
    protected $_dbDataset;

    /**
     * MySqlCommand constructor.
     *
     * @param DBDataset $_dbDataset
     */
    public function __construct(DBDataset $_dbDataset)
    {
        $this->_dbDataset = $_dbDataset;
    }

    /**
     * @return DBDataset
     */
    public function getDbDataset()
    {
        return $this->_dbDataset;
    }

    public function createDatabase()
    {
        $database = $this->getDbDataset()->getConnectionManagement()->getDatabase();

        $this->getDbDataset()->execSQL("CREATE SCHEMA IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 ;");
        $this->getDbDataset()->execSQL("USE `$database`");
    }

    public function dropDatabase()
    {
        $database = $this->getDbDataset()->getConnectionManagement()->getDatabase();

        $this->getDbDataset()->execSQL("drop database `$database`");
    }

    public function getVersion()
    {
        return $this->getDbDataset()->getScalar('SELECT version FROM migration_table');
    }

    public function setVersion($version)
    {
        $this->getDbDataset()->execSQL('UPDATE migration_table SET version = [[version]]', ['version' => $version]);
    }

    public function createVersion()
    {
        $this->getDbDataset()->execSQL('CREATE TABLE IF NOT EXISTS migration_table (version int)');

        // Get the version to check if exists
        $version = $this->getVersion();
        if ($version === false) {
            $this->getDbDataset()->execSQL('insert into migration_table values(0)');
        }
    }

}