<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\DbMigration\Migration;
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

    /**
     * @return string
     */
    public function getMigrationTable(): string
    {
        return $this->migrationTable;
    }

    /**
     * @return DbDriverInterface
     */
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
     * @param string $version
     * @param string $status
     */
    public function setVersion(string $version, string $status): void
    {
        $this->getDbDriver()->execute(
            'UPDATE ' . $this->getMigrationTable() . ' SET version = :version, status = :status',
            [
                'version' => $version,
                'status' => $status,
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
                Migration::VERSION_STATUS_UNKNOWN)
            );
        }
    }

    /**
     *
     */
    public function updateVersionTable(): void
    {
        $currentVersion = $this->getDbDriver()->getScalar(sprintf('select version from %s', $this->getMigrationTable()));
        $this->getDbDriver()->execute(sprintf('drop table %s', $this->getMigrationTable()));
        $this->createVersion();
        $this->setVersion($currentVersion, Migration::VERSION_STATUS_UNKNOWN);
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

    public function isDatabaseVersioned(): bool
    {
        return $this->isTableExists(ltrim($this->getDbDriver()->getUri()->getPath(), "/"), $this->getMigrationTable());
    }

    public function supportsTransaction(): bool
    {
        return true;
    }
}
