<?php

namespace ByJG\DbMigration;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Factory;
use ByJG\DbMigration\Commands\CommandInterface;
use ByJG\DbMigration\Exception\DatabaseIsIncompleteException;
use ByJG\Util\Uri;

class Migration
{
    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var string
     */
    protected $_folder;

    /**
     * @var DbDriverInterface
     */
    protected $dbDriver;

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
     * @param Uri $uri
     * @param string $_folder
     */
    public function __construct(Uri $uri, $_folder)
    {
        $this->uri = $uri;
        $this->_folder = $_folder;

        if (!file_exists($this->_folder . '/base.sql')) {
            throw new \InvalidArgumentException("Migration script '{$this->_folder}/base.sql' not found");
        }
    }

    /**
     * @return DbDriverInterface
     */
    public function getDbDriver()
    {
        if (is_null($this->dbDriver)) {
            $this->dbDriver = Factory::getDbRelationalInstance($this->uri->__toString());
        }
        return $this->dbDriver;
    }

    /**
     * @return CommandInterface
     */
    public function getDbCommand()
    {
        if (is_null($this->_dbCommand)) {
            $class = $this->getCommandClassName();
            $this->_dbCommand = new $class($this->getDbDriver());
        }
        return $this->_dbCommand;
    }

    protected function getCommandClassName()
    {
        return "\\ByJG\\DbMigration\\Commands\\" . ucfirst($this->uri->getScheme()) . "Command";
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
        $result = glob(
            $this->_folder
            . "/migrations"
            . "/" . ($increment < 0 ? "down" : "up")
            . "/*$version.sql"
        );

        foreach ($result as $file) {
            if (intval(basename($file)) == $version) {
                return $file;
            }
        }
    }

    /**
     * Create the database it it does not exists. Does not use this methos in a production environment; 
     */
    public function prepareEnvironment()
    {
        $class = $this->getCommandClassName();
        $class::prepareEnvironment($this->uri);
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
        $this->getDbCommand()->createVersion();
        $this->getDbCommand()->executeSql(file_get_contents($this->getBaseSql()));
        $this->getDbCommand()->setVersion(0, 'complete');
        $this->up($upVersion);
    }

    public function createVersion()
    {
        $this->getDbCommand()->createVersion();
    }

    public function updateTableVersion()
    {
        $this->getDbCommand()->updateVersionTable();
    }

    /**
     * Get the current database version
     *
     * @return int
     */
    public function getCurrentVersion()
    {
        return $this->getDbCommand()->getVersion();;
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
     * @param bool $force
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     */
    protected function migrate($upVersion, $increment, $force)
    {
        $versionInfo = $this->getCurrentVersion();
        $currentVersion = intval($versionInfo['version']) + $increment;

        if (strpos($versionInfo['status'], 'partial') !== false && !$force) {
            throw new DatabaseIsIncompleteException('Database was not fully updated. Use --force for migrate.');
        }

        while ($this->canContinue($currentVersion, $upVersion, $increment)
            && file_exists($file = $this->getMigrationSql($currentVersion, $increment))
        ) {
            if ($this->_callableProgress) {
                call_user_func_array($this->_callableProgress, ['migrate', $currentVersion]);
            }

            $this->getDbCommand()->setVersion($currentVersion, 'partial ' . ($increment>0 ? 'up' : 'down'));
            $this->getDbCommand()->executeSql(file_get_contents($file));
            $this->getDbCommand()->setVersion($currentVersion, 'complete');
            $currentVersion = $currentVersion + $increment;
        }
    }

    /**
     * Run all scripts to up the database version from current up to latest version or the specified version.
     *
     * @param int $upVersion
     * @param bool $force
     */
    public function up($upVersion = null, $force = false)
    {
        $this->migrate($upVersion, 1, $force);
    }

    /**
     * Run all scripts to down the database version from current version up to the specified version.
     *
     * @param int $upVersion
     * @param bool $force
     */
    public function down($upVersion, $force = false)
    {
        $this->migrate($upVersion, -1, $force);
    }
    
    public function addCallbackProgress(Callable $callable)
    {
        $this->_callableProgress = $callable;
    }
}
