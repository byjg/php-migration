<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\DbMigration\Migration;
use Psr\Http\Message\UriInterface;

abstract class AbstractDatabase implements DatabaseInterface
{
    /**
     * @var DbDriverInterface
     */
    private $dbDriver;

    /**
     * @var UriInterface
     */
    private $uri;
    /**
     * @var string
     */
    private $migrationTable;

    /**
     * Command constructor.
     *
     * @param UriInterface $uri
     * @param string $migrationTable
     */
    public function __construct(UriInterface $uri, $migrationTable = 'migration_version')
    {
        $this->uri = $uri;
        $this->migrationTable = $migrationTable;
    }

    /**
     * @return string
     */
    public function getMigrationTable()
    {
        return $this->migrationTable;
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
     * @return array
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    public function getVersion()
    {
        $result = [];
        try {
            $result['version'] = $this->getDbDriver()->getScalar('SELECT version FROM ' . $this->getMigrationTable());
        } catch (\Exception $ex) {
            throw new DatabaseNotVersionedException('This database does not have a migration version. Please use "migrate reset" or "migrate install" to create one.');
        }

        try {
            $result['status'] = $this->getDbDriver()->getScalar('SELECT status FROM ' . $this->getMigrationTable());
        } catch (\Exception $ex) {
            throw new OldVersionSchemaException('This database does not have a migration version. Please use "migrate install" for update it.');
        }

        return $result;
    }

    /**
     * @param $version
     * @param $status
     */
    public function setVersion($version, $status)
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
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    protected function checkExistsVersion()
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
    public function updateVersionTable()
    {
        $currentVersion = $this->getDbDriver()->getScalar(sprintf('select version from %s', $this->getMigrationTable()));
        $this->getDbDriver()->execute(sprintf('drop table %s', $this->getMigrationTable()));
        $this->createVersion();
        $this->setVersion($currentVersion, Migration::VERSION_STATUS_UNKNOWN);
    }
}
