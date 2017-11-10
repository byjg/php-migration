<?php

namespace ByJG\DbMigration;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Factory;
use ByJG\DbMigration\Database\DatabaseInterface;
use ByJG\DbMigration\Exception\DatabaseIsIncompleteException;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
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
    protected $folder;

    /**
     * @var DbDriverInterface
     */
    protected $dbDriver;

    /**
     * @var DatabaseInterface
     */
    protected $dbCommand;

    /**
     * @var Callable
     */
    protected $callableProgress;

    /**
     * Migration constructor.
     *
     * @param Uri $uri
     * @param string $folder
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     */
    public function __construct(Uri $uri, $folder)
    {
        $this->uri = $uri;
        $this->folder = $folder;

        if (!file_exists($this->folder . '/base.sql')) {
            throw new InvalidMigrationFile("Migration script '{$this->folder}/base.sql' not found");
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
     * @return DatabaseInterface
     */
    public function getDbCommand()
    {
        if (is_null($this->dbCommand)) {
            $class = $this->getDatabaseClassName();
            $this->dbCommand = new $class($this->getDbDriver());
        }
        return $this->dbCommand;
    }

    protected function getDatabaseClassName()
    {
        return "\\ByJG\\DbMigration\\Database\\" . ucfirst($this->uri->getScheme()) . "Database";
    }

    /**
     * Get the full path and name of the "base.sql" script
     *
     * @return string
     */
    public function getBaseSql()
    {
        return $this->folder . "/base.sql";
    }

    /**
     * Get the full path script based on the version
     *
     * @param $version
     * @param $increment
     * @return string
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     */
    public function getMigrationSql($version, $increment)
    {
        // I could use the GLOB_BRACE but it is not supported on ALPINE distros.
        // So, I have to call multiple times to simulate the braces.

        if (intval($version) != $version) {
            throw new \InvalidArgumentException("Version '$version' should be a integer number");
        }
        $version = intval($version);

        $filePattern = $this->folder
            . "/migrations"
            . "/" . ($increment < 0 ? "down" : "up")
            . "/*.sql";

        $result = array_filter(glob($filePattern), function ($file) use ($version) {
            return preg_match("/^0*$version(-dev)?\.sql$/", basename($file));
        });

        // Valid values are 0 or 1
        if (count($result) > 1) {
            throw new InvalidMigrationFile("You have two files with the same version number '$version'");
        }

        foreach ($result as $file) {
            if (intval(basename($file)) === $version) {
                return $file;
            }
        }
        return null;
    }

    /**
     * Create the database it it does not exists. Does not use this methos in a production environment
     */
    public function prepareEnvironment()
    {
        $class = $this->getDatabaseClassName();
        $class::prepareEnvironment($this->uri);
    }
    
    /**
     * Restore the database using the "base.sql" script and run all migration scripts
     * Note: the database must exists. If dont exist run the method prepareEnvironment
     *
     * @param int $upVersion
     */
    public function reset($upVersion = null)
    {
        if ($this->callableProgress) {
            call_user_func_array($this->callableProgress, ['reset', 0]);
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
        return $this->getDbCommand()->getVersion();
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
        $compareVersion =
            intval($currentVersion) < intval($upVersion)
                ? -1
                : (
                    intval($currentVersion) > intval($upVersion)
                        ? 1
                        : 0
                );

        return !($existsUpVersion && ($compareVersion === intval($increment)));
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
            if ($this->callableProgress) {
                call_user_func_array($this->callableProgress, ['migrate', $currentVersion]);
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
     * Run all scripts to up or down the database version from current up to latest version or the specified version.
     *
     * @param int $upVersion
     * @param bool $force
     */
    public function update($upVersion = null, $force = false)
    {
        $versionInfo = $this->getCurrentVersion();
        $version = intval($versionInfo['version']);
        $increment = 1;
        if ($upVersion !== null && $upVersion < $version) {
            $increment = -1;
        }
        $this->migrate($upVersion, $increment, $force);
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

    /**
     * @param callable $callable
     */
    public function addCallbackProgress(callable $callable)
    {
        $this->callableProgress = $callable;
    }
}
