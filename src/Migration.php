<?php

namespace ByJG\DbMigration;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\AnyDataset\Repository\DBDataset;
use ByJG\DbMigration\Commands\CommandInterface;

class Migration
{
    /**
     * @var ConnectionManagement
     */
    protected $_connection;

    /**
     * @var string
     */
    protected $_folder;

    /**
     * @var DBDataset
     */
    protected $_dbDataset;

    /**
     * @var CommandInterface
     */
    protected $_dbCommand;
    
    /**
     * Migration constructor.
     *
     * @param ConnectionManagement $_connection
     * @param string $_folder
     */
    public function __construct(ConnectionManagement $_connection, $_folder)
    {
        $this->_connection = $_connection;
        $this->_folder = $_folder;

        if (!file_exists($this->_folder) || !is_dir($this->_folder)) {
            throw new \InvalidArgumentException("Base migrations directory '{$this->_folder}' not found");
        }
    }

    /**
     * @return DBDataset
     */
    public function getDbDataset()
    {
        if (is_null($this->_dbDataset)) {
            $this->_dbDataset = new DBDataset($this->_connection->getDbConnectionString());
        }
        return $this->_dbDataset;
    }

    /**
     * @return CommandInterface
     */
    public function getDbCommand()
    {
        if (is_null($this->_dbCommand)) {
            $class = "\\ByJG\\DbMigration\\Commands\\" . ucfirst($this->_connection->getDriver()) . "Command";
            $this->_dbCommand = new $class($this->getDbDataset());
        }
        return $this->_dbCommand;
    }

    public function getBaseSql()
    {
        return $this->_folder . "/base.sql";
    }

    public function getMigrationSql($version, $increment)
    {
        return $this->_folder 
            . "/migrations" 
            . "/" . ($increment < 0 ? "down" : "up")
            . "/" . str_pad($version, 5, '0', STR_PAD_LEFT) . ".sql";
    }
    
    public function reset($upVersion = null)
    {
        $this->getDbCommand()->dropDatabase();
        $this->getDbCommand()->createDatabase();
        $this->getDbDataset()->execSQL(file_get_contents($this->getBaseSql()));
        $this->getDbCommand()->createVersion();
        $this->up($upVersion);
    }
    
    public function getCurrentVersion()
    {
        return intval($this->getDbCommand()->getVersion());
    }

    protected function canContinue($currentVersion, $upVersion, $increment)
    {
        $existsUpVersion = ($upVersion !== null);
        $compareVersion = strcmp(
                str_pad($currentVersion, 5, '0', STR_PAD_LEFT),
                str_pad($upVersion, 5, '0', STR_PAD_LEFT)
            ) == $increment;

        return !($existsUpVersion && $compareVersion);
    }
    
    protected function migrate($upVersion, $increment)
    {
        $currentVersion = $this->getCurrentVersion() + $increment;
        
        while ($this->canContinue($currentVersion, $upVersion, $increment)
            && file_exists($file = $this->getMigrationSql($currentVersion, $increment))
        ) {
            $this->getDbDataset()->execSQL(file_get_contents($file));
            $this->getDbCommand()->setVersion($currentVersion++);
        }
    }

    public function up($upVersion = null)
    {
        $this->migrate($upVersion, 1);
    }

    public function down($upVersion = null)
    {
        $this->migrate($upVersion, -1);
    }
}