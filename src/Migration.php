<?php

namespace ByJG\DbMigration;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\DbMigration\Database\DatabaseInterface;
use ByJG\DbMigration\Exception\DatabaseDoesNotRegistered;
use ByJG\DbMigration\Exception\DatabaseIsIncompleteException;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Migration
{
    const VERSION_STATUS_UNKNOWN = "unknown";
    const VERSION_STATUS_PARTIAL = "partial";
    const VERSION_STATUS_COMPLETE = "complete";

    /**
     * @var UriInterface
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
     * @var array
     */
    protected static $databases = [];
    /**
     * @var string
     */
    private $migrationTable;

    /**
     * Migration constructor.
     *
     * @param UriInterface $uri
     * @param string $folder
     * @param bool $requiredBase Define if base.sql is required
     * @param string $migrationTable
     * @throws InvalidMigrationFile
     */
    public function __construct(UriInterface $uri, $folder, $requiredBase = true, $migrationTable = 'migration_version')
    {
        $this->uri = $uri;
        $this->folder = $folder;
        if ($requiredBase && !file_exists($this->folder . '/base.sql')) {
            throw new InvalidMigrationFile("Migration script '{$this->folder}/base.sql' not found");
        }
        $this->migrationTable = $migrationTable;
    }

    /**
     * @param $scheme
     * @param $className
     * @return $this
     */
    public static function registerDatabase($class)
    {
        if (!in_array(DatabaseInterface::class, class_implements($class))) {
            throw new InvalidArgumentException('Class not implements DatabaseInterface!');
        }

        $protocolList = $class::schema();
        foreach ((array)$protocolList as $item) {
            self::$databases[$item] = $class;
        }
    }

    /**
     * @return DbDriverInterface
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     */
    public function getDbDriver()
    {
        return $this->getDbCommand()->getDbDriver();
    }

    /**
     * @return DatabaseInterface
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     */
    public function getDbCommand()
    {
        if (is_null($this->dbCommand)) {
            $class = $this->getDatabaseClassName();
            $this->dbCommand = new $class($this->uri, $this->migrationTable);
        }
        return $this->dbCommand;
    }

    public function getMigrationTable()
    {
        return $this->migrationTable;
    }

    /**
     * @return mixed
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     */
    protected function getDatabaseClassName()
    {
        if (isset(self::$databases[$this->uri->getScheme()])) {
            return self::$databases[$this->uri->getScheme()];
        }
        throw new DatabaseDoesNotRegistered(
            'Scheme "' . $this->uri->getScheme() . '" does not found. Did you registered it?'
        );
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
            return preg_match("/^0*$version(-[\w\d-]*)?\.sql$/", basename($file));
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
     * Get the file contents and metainfo
     * @param $file
     * @return array
     */
    public function getFileContent($file)
    {
        $data = [
            "file" => $file,
            "description" => "no description provided. Pro tip: use `-- @description:` to define one.",
            "exists" => false,
            "checksum" => null,
            "content" => null,
        ];
        if (empty($file) || !file_exists($file)) {
            return $data;
        }

        $data["content"] = file_get_contents($file);

        if (preg_match("/--\s*@description:\s*(?<name>.*)/", $data["content"], $description)) {
            $data["description"] = $description["name"];
        }

        $data["exists"] = true;
        $data["checksum"] = sha1($data["content"]);

        return $data;
    }

    /**
     * Create the database it it does not exists. Does not use this methos in a production environment
     *
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
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
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    public function reset($upVersion = null)
    {
        $fileInfo = $this->getFileContent($this->getBaseSql());

        if ($this->callableProgress) {
            call_user_func_array($this->callableProgress, ['reset', 0, $fileInfo]);
        }
        $this->getDbCommand()->dropDatabase();
        $this->getDbCommand()->createDatabase();
        $this->getDbCommand()->createVersion();

        if ($fileInfo["exists"]) {
            $this->getDbCommand()->executeSql($fileInfo["content"]);
        }

        $this->getDbCommand()->setVersion(0, Migration::VERSION_STATUS_COMPLETE);
        $this->up($upVersion);
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     */
    public function createVersion()
    {
        $this->getDbCommand()->createVersion();
    }

    /**
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     */
    public function updateTableVersion()
    {
        $this->getDbCommand()->updateVersionTable();
    }

    /**
     * Get the current database version
     *
     * @return string[] The current 'version' and 'status' as an associative array
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
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
                : (intval($currentVersion) > intval($upVersion) ? 1 : 0);

        return !($existsUpVersion && ($compareVersion === intval($increment)));
    }

    /**
     * Method for execute the migration.
     *
     * @param int $upVersion
     * @param int $increment Can accept 1 for UP or -1 for down
     * @param bool $force
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    protected function migrate($upVersion, $increment, $force)
    {
        $versionInfo = $this->getCurrentVersion();
        $currentVersion = intval($versionInfo['version']) + $increment;

        if (strpos($versionInfo['status'], Migration::VERSION_STATUS_PARTIAL) !== false && !$force) {
            throw new DatabaseIsIncompleteException('Database was not fully updated. Use --force for migrate.');
        }

        while ($this->canContinue($currentVersion, $upVersion, $increment)
        ) {
            $fileInfo = $this->getFileContent($this->getMigrationSql($currentVersion, $increment));

            if (!$fileInfo["exists"]) {
                break;
            }

            if ($this->callableProgress) {
                call_user_func_array($this->callableProgress, ['migrate', $currentVersion, $fileInfo]);
            }

            $this->getDbCommand()->setVersion($currentVersion, Migration::VERSION_STATUS_PARTIAL . ' ' . ($increment>0 ? 'up' : 'down'));
            $this->getDbCommand()->executeSql($fileInfo["content"]);
            $this->getDbCommand()->setVersion($currentVersion, Migration::VERSION_STATUS_COMPLETE);
            $currentVersion = $currentVersion + $increment;
        }
    }

    /**
     * Run all scripts to up the database version from current up to latest version or the specified version.
     *
     * @param int $upVersion
     * @param bool $force
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
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
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
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
     * @throws \ByJG\DbMigration\Exception\DatabaseDoesNotRegistered
     * @throws \ByJG\DbMigration\Exception\DatabaseIsIncompleteException
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\InvalidMigrationFile
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
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

    public function isDatabaseVersioned()
    {
        return $this->getDbCommand()->isDatabaseVersioned();
    }
}
