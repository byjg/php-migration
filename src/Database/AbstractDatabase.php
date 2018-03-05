<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;

abstract class AbstractDatabase implements DatabaseInterface
{
    /**
     * @var DbDriverInterface
     */
    private $dbDriver;

    /**
     * Command constructor.
     *
     * @param DbDriverInterface $dbDriver
     */
    public function __construct(DbDriverInterface $dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }

    /**
     * @return DbDriverInterface
     */
    public function getDbDriver()
    {
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
            $result['version'] = $this->getDbDriver()->getScalar('SELECT version FROM migration_version');
        } catch (\Exception $ex) {
            throw new DatabaseNotVersionedException('This database does not have a migration version. Please use "migrate reset" or "migrate install" to create one.');
        }

        try {
            $result['status'] = $this->getDbDriver()->getScalar('SELECT status FROM migration_version');
        } catch (\Exception $ex) {
            throw new OldVersionSchemaException('This database does not have a migration version. Please use "migrate install" for update it.');
        }

        return $result;
    }

    public function setVersion($version, $status)
    {
        $this->getDbDriver()->execute(
            'UPDATE migration_version SET version = :version, status = :status',
            [
                'version' => $version,
                'status' => $status
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
        if (empty($versionInfo['version'])) {
            $this->getDbDriver()->execute("insert into migration_version values(0, 'unknow')");
        }
    }

    public function updateVersionTable()
    {
        $currentVersion = $this->getDbDriver()->getScalar('select version from migration_version');
        $this->getDbDriver()->execute('drop table migration_version');
        $this->createVersion();
        $this->setVersion($currentVersion, 'unknow');
    }
}
