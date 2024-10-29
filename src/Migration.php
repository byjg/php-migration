<?php

namespace ByJG\DbMigration;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\DbMigration\Database\DatabaseInterface;
use ByJG\DbMigration\Exception\DatabaseDoesNotRegistered;
use ByJG\DbMigration\Exception\DatabaseIsIncompleteException;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use Closure;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Migration
{
    /**
     * @var UriInterface
     */
    protected UriInterface $uri;

    /**
     * @var string
     */
    protected string $folder;

    /**
     * @var DbDriverInterface
     */
    protected DbDriverInterface $dbDriver;

    /**
     * @var DatabaseInterface|null
     */
    protected ?DatabaseInterface $dbCommand = null;

    /**
     * @var Closure|null
     */
    protected ?Closure $callableProgress = null;

    /**
     * @var array
     */
    protected static array $databases = [];
    /**
     * @var string
     */
    private string $migrationTable;

    private bool $transaction = false;

    /**
     * Migration constructor.
     *
     * @param UriInterface $uri
     * @param string $folder
     * @param bool $requiredBase Define if base.sql is required
     * @param string $migrationTable
     * @throws InvalidMigrationFile
     */
    public function __construct(UriInterface $uri, string $folder, bool $requiredBase = true, string $migrationTable = 'migration_version')
    {
        $this->uri = $uri;
        $this->folder = $folder;
        if ($requiredBase && !file_exists($this->folder . '/base.sql')) {
            throw new InvalidMigrationFile("Migration script '{$this->folder}/base.sql' not found");
        }
        $this->migrationTable = $migrationTable;
    }

    public function withTransactionEnabled($enabled = true): static
    {
        $this->transaction = $enabled;
        return $this;
    }

    /**
     * @param string $class
     */
    public static function registerDatabase(string $class): void
    {
        if (!in_array(DatabaseInterface::class, class_implements($class))) {
            throw new InvalidArgumentException('Class not implements DatabaseInterface!');
        }

        /** @var DatabaseInterface $class */
        $protocolList = $class::schema();
        foreach ((array)$protocolList as $item) {
            self::$databases[$item] = $class;
        }
    }

    /**
     * @return DbDriverInterface
     * @throws DatabaseDoesNotRegistered
     */
    public function getDbDriver(): DbDriverInterface
    {
        return $this->getDbCommand()->getDbDriver();
    }

    /**
     * @return DatabaseInterface
     * @throws DatabaseDoesNotRegistered
     */
    public function getDbCommand(): DatabaseInterface
    {
        if (is_null($this->dbCommand)) {
            $class = $this->getDatabaseClassName();
            $this->dbCommand = new $class($this->uri, $this->migrationTable);
        }
        return $this->dbCommand;
    }

    public function getMigrationTable(): string
    {
        return $this->migrationTable;
    }

    /**
     * @return mixed
     * @throws DatabaseDoesNotRegistered
     */
    protected function getDatabaseClassName(): mixed
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
    public function getBaseSql(): string
    {
        return $this->folder . "/base.sql";
    }

    /**
     * Get the full path script based on the version
     *
     * @param int $version
     * @param int $increment
     * @return string|null
     * @throws InvalidMigrationFile
     */
    public function getMigrationSql(int $version, int $increment): ?string
    {
        // I could use the GLOB_BRACE, but it is not supported on ALPINE distros.
        // So, I have to call multiple times to simulate the braces.

        if (intval($version) != $version) {
            throw new InvalidArgumentException("Version '$version' should be a integer number");
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
    public function getFileContent($file): array
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
     * Create the database it is not exists. Does not use these methods in a production environment
     *
     * @throws DatabaseDoesNotRegistered
     */
    public function prepareEnvironment(): void
    {
        $class = $this->getDatabaseClassName();
        $class::prepareEnvironment($this->uri);
    }

    /**
     * Restore the database using the "base.sql" script and run all migration scripts
     * Note: the database must exist. If it don't exist run the method prepareEnvironment
     *
     * @param int|null $upVersion
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
     */
    public function reset(int $upVersion = null): void
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

        $this->getDbCommand()->setVersion(0, MigrationStatus::complete);
        $this->up($upVersion);
    }

    /**
     * @throws DatabaseDoesNotRegistered
     */
    public function createVersion(): void
    {
        $this->getDbCommand()->createVersion();
    }

    /**
     * @throws DatabaseDoesNotRegistered
     */
    public function updateTableVersion(): void
    {
        $this->getDbCommand()->updateVersionTable();
    }

    /**
     * Get the current database version
     *
     * @return string[] The current 'version' and 'status' as an associative array
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    public function getCurrentVersion(): array
    {
        return $this->getDbCommand()->getVersion();
    }

    /**
     * @param $currentVersion
     * @param $upVersion
     * @param $increment
     * @return bool
     */
    protected function canContinue(int $currentVersion, ?int $upVersion, int $increment): bool
    {
        $existsUpVersion = ($upVersion !== null);
        $compareVersion =
            $currentVersion < intval($upVersion)
                ? -1
                : ($currentVersion > intval($upVersion) ? 1 : 0);

        return !($existsUpVersion && ($compareVersion === $increment));
    }

    /**
     * Method for execute the migration.
     *
     * @param int|null $upVersion
     * @param int $increment Can accept 1 for UP or -1 for down
     * @param bool $force
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
     */
    protected function migrate(?int $upVersion, int $increment, bool $force): void
    {
        $versionInfo = $this->getCurrentVersion();
        $currentVersion = intval($versionInfo['version']) + $increment;

        if (in_array($versionInfo['status'], [MigrationStatus::partialUp, MigrationStatus::partialDown]) && !$force) {
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

            $useTransaction = $this->transaction && $this->getDbCommand()->supportsTransaction();

            try {
                if ($useTransaction) {
                    $this->getDbDriver()->beginTransaction();
                }
                $this->getDbCommand()->setVersion($currentVersion, $increment>0 ? MigrationStatus::partialUp : MigrationStatus::partialDown);
                $this->getDbCommand()->executeSql($fileInfo["content"]);
                $this->getDbCommand()->setVersion($currentVersion, MigrationStatus::complete);
                if ($useTransaction) {
                    $this->getDbDriver()->commitTransaction();
                }
            } catch (Exception $e) {
                if ($useTransaction) {
                    $this->getDbDriver()->rollbackTransaction();
                }
                throw $e;
            }
            $currentVersion = $currentVersion + $increment;
        }
    }

    /**
     * Run all scripts to up the database version from current up to latest version or the specified version.
     *
     * @param int|null $upVersion
     * @param bool $force
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
     */
    public function up(?int $upVersion = null, bool $force = false): void
    {
        $this->migrate($upVersion, 1, $force);
    }

    /**
     * Run all scripts to up or down the database version from current up to latest version or the specified version.
     *
     * @param int|null $upVersion
     * @param bool $force
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
     */
    public function update(?int $upVersion = null, bool $force = false): void
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
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseIsIncompleteException
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws OldVersionSchemaException
     */
    public function down(int $upVersion, bool $force = false): void
    {
        $this->migrate($upVersion, -1, $force);
    }

    /**
     * @param callable $callable
     */
    public function addCallbackProgress(callable $callable): void
    {
        $this->callableProgress = $callable;
    }

    /**
     * @throws DatabaseDoesNotRegistered
     */
    public function isDatabaseVersioned(): bool
    {
        return $this->getDbCommand()->isDatabaseVersioned();
    }
}
