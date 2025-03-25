<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\DbMigration\MigrationStatus;
use ByJG\Util\Uri;
use Exception;
use Psr\Http\Message\UriInterface;

abstract class AbstractDatabase implements DatabaseInterface
{
    /**
     * @var DbDriverInterface|null
     */
    private ?DbDriverInterface $dbDriver = null;

    /**
     * @var UriInterface
     */
    private UriInterface $uri;
    /**
     * @var string
     */
    private string $migrationTable;

    /**
     * Command constructor.
     *
     * @param UriInterface $uri
     * @param string $migrationTable
     */
    public function __construct(UriInterface $uri, string $migrationTable = 'migration_version')
    {
        $this->uri = $uri;
        $this->migrationTable = $migrationTable;
    }

    protected static function getDatabaseName(Uri $uri): string
    {
        return ltrim($uri->getPath(), '/');
    }

    protected static function getDbDriverWithoutDatabase(UriInterface $uri, string $database = ''): DbDriverInterface
    {
        return Factory::getDbInstance($uri->withPath("/$database")->__toString());
    }

    /**
     * @return string
     */
    #[\Override]
    public function getMigrationTable(): string
    {
        return $this->migrationTable;
    }

    /**
     * @return DbDriverInterface
     */
    #[\Override]
    public function getDbDriver(): DbDriverInterface
    {
        if (is_null($this->dbDriver)) {
            $this->dbDriver = Factory::getDbInstance($this->uri->__toString());
        }
        return $this->dbDriver;
    }

    /**
     * @return array
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    #[\Override]
    public function getVersion(): array
    {
        $result = [];
        try {
            $result['version'] = $this->getDbDriver()->getScalar('SELECT version FROM ' . $this->getMigrationTable());
        } catch (Exception $ex) {
            throw new DatabaseNotVersionedException('This database does not have a migration version. Please use "migrate reset" or "migrate install" to create one.');
        }

        try {
            $result['status'] = $this->getDbDriver()->getScalar('SELECT status FROM ' . $this->getMigrationTable());
        } catch (Exception $ex) {
            throw new OldVersionSchemaException('This database does not have a migration version. Please use "migrate install" for update it.');
        }

        return $result;
    }

    /**
     * @param int $version
     * @param MigrationStatus $status
     */
    #[\Override]
    public function setVersion(int $version, MigrationStatus $status): void
    {
        $this->getDbDriver()->execute(
            'UPDATE ' . $this->getMigrationTable() . ' SET version = :version, status = :status',
            [
                'version' => $version,
                'status' => $status->value,
            ]
        );
    }

    /**
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    protected function checkExistsVersion(): void
    {
        // Get the version to check if exists
        $versionInfo = $this->getVersion();
        if ($versionInfo['version'] === false) {
            $this->getDbDriver()->execute(sprintf(
                "insert into %s values(0, '%s')",
                $this->getMigrationTable(),
                MigrationStatus::unknown->value)
            );
        }
    }

    /**
     *
     */
    #[\Override]
    public function updateVersionTable(): void
    {
        $currentVersion = $this->getDbDriver()->getScalar(sprintf('select version from %s', $this->getMigrationTable()));
        $this->getDbDriver()->execute(sprintf('drop table %s', $this->getMigrationTable()));
        $this->createVersion();
        $this->setVersion($currentVersion, MigrationStatus::unknown);
    }

    protected function isTableExists(?string $schema, string $table): bool
    {
        $count = $this->getDbDriver()->getScalar(
            'SELECT count(*) FROM information_schema.tables ' .
            ' WHERE table_schema = :schema ' .
            '  AND table_name = :table ',
            [
                "schema" => $schema,
                "table" => $table
            ]
        );

        return (intval($count) !== 0);
    }

    #[\Override]
    public function isDatabaseVersioned(): bool
    {
        return $this->isTableExists(static::getDatabaseName($this->getDbDriver()->getUri()), $this->getMigrationTable());
    }

    #[\Override]
    public function supportsTransaction(): bool
    {
        return true;
    }
}
