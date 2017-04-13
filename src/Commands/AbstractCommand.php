<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\DbDriverInterface;

abstract class AbstractCommand implements CommandInterface
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

    public function getVersion()
    {
        try {
            return $this->getDbDriver()->getScalar('SELECT version FROM migration_version');
        } catch (\Exception $ex) {
            throw new \Exception('This database does not have a migration version. Please use "migrate reset" to create one.');
        }
    }

    public function setVersion($version)
    {
        $this->getDbDriver()->execute('UPDATE migration_version SET version = :version', ['version' => $version]);
    }

    protected function checkExistsVersion()
    {
        // Get the version to check if exists
        $version = $this->getVersion();
        if (empty($version)) {
            $this->getDbDriver()->execute('insert into migration_version values(0)');
        }
    }
}
