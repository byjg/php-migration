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
     * @var Callable
     */
    protected $_callableProgress;
    
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

        if (!file_exists($this->_folder . '/base.sql')) {
            throw new \InvalidArgumentException("Migration script '{$this->_folder}/base.sql' not found");
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
            $class = $this->getCommandClassName();
            $this->_dbCommand = new $class($this->getDbDataset());
        }
        return $this->_dbCommand;
    }

    protected function getCommandClassName()
    {
        return "\\ByJG\\DbMigration\\Commands\\" . ucfirst($this->_connection->getDriver()) . "Command";
    }

    /**
     * Get the full path and name of the "base.sql" script
     *
     * @return string
     */
    public function getBaseSql()
    {
        return $this->_folder . "/base.sql";
    }

    /**
     * Get the full path script based on the version
     *
     * @param $version
     * @param $increment
     * @return string
     */
    public function getMigrationSql($version, $increment)
    {
        return $this->_folder 
            . "/migrations" 
            . "/" . ($increment < 0 ? "down" : "up")
            . "/" . str_pad($version, 5, '0', STR_PAD_LEFT) . ".sql";
    }

    /**
     * Create the database it it does not exists. Does not use this methos in a production environment; 
     */
    public function prepareEnvironment()
    {
        $class = $this->getCommandClassName();
        $class::prepareEnvironment($this->_connection);
    }
    
    /**
     * Restore the database using the "base.sql" script and run all migration scripts
     * Note: the database must exists. If dont exist run the method prepareEnvironment.  
     *
     * @param int $upVersion
     */
    public function reset($upVersion = null)
    {
        if ($this->_callableProgress) {
            call_user_func_array($this->_callableProgress, ['reset', 0]);
        }
        $this->getDbCommand()->dropDatabase();
        $this->getDbCommand()->createDatabase();
        $this->getDbCommand()->executeSql(file_get_contents($this->getBaseSql()));
        $this->getDbCommand()->createVersion();
        $this->up($upVersion);
    }

    /**
     * Get the current database version
     *
     * @return int
     */
    public function getCurrentVersion()
    {
        return intval($this->getDbCommand()->getVersion());
    }

    /**
     * @param $currentVersion
     * @param $upVersion
     * @param $increment
     * @return bool
     */
    protected function canContinue($currentVersion, $upVersion, $increment)
    {
        $existsUpVersion = ($upVersion !== null);
        $compareVersion = strcmp(
                str_pad($currentVersion, 5, '0', STR_PAD_LEFT),
                str_pad($upVersion, 5, '0', STR_PAD_LEFT)
            ) == $increment;

        return !($existsUpVersion && $compareVersion);
    }

    /**
     * Method for execute the migration.
     *
     * @param int $upVersion
     * @param int $increment Can accept 1 for UP or -1 for down
     */
    protected function migrate($upVersion, $increment)
    {
        $currentVersion = $this->getCurrentVersion() + $increment;
        
        while ($this->canContinue($currentVersion, $upVersion, $increment)
            && file_exists($file = $this->getMigrationSql($currentVersion, $increment))
        ) {
            if ($this->_callableProgress) {
                call_user_func_array($this->_callableProgress, ['migrate', $currentVersion]);
            }

            $this->getDbCommand()->executeSql(file_get_contents($file));
            $this->getDbCommand()->setVersion($currentVersion);
            $currentVersion = $currentVersion + $increment;
        }
    }

    /**
     * Run all scripts to up the database version from current up to latest version or the specified version.
     *
     * @param int $upVersion
     */
    public function up($upVersion = null)
    {
        $this->migrate($upVersion, 1);
    }

    /**
     * Run all scripts to down the database version from current version up to the specified version.
     *
     * @param int $upVersion
     */
    public function down($upVersion)
    {
        $this->migrate($upVersion, -1);
    }
    
    public function addCallbackProgress(Callable $callable)
    {
        $this->_callableProgress = $callable;
    }
}
