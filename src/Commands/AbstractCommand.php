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
        return $this->getDbDriver()->getScalar('SELECT version FROM migration_version');
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
