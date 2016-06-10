<?php

namespace ByJG\DbMigration\Commands;

use ByJG\AnyDataset\Repository\DBDataset;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * @var DBDataset
     */
    private $_dbDataset;

    /**
     * Command constructor.
     *
     * @param DBDataset $_dbDataset
     */
    public function __construct(DBDataset $_dbDataset)
    {
        $this->_dbDataset = $_dbDataset;
    }

    /**
     * @return DBDataset
     */
    public function getDbDataset()
    {
        return $this->_dbDataset;
    }

    public function getVersion()
    {
        return $this->getDbDataset()->getScalar('SELECT version FROM migration_version');
    }

    public function setVersion($version)
    {
        $this->getDbDataset()->execSQL('UPDATE migration_version SET version = :version', ['version' => $version]);
    }

    protected function checkExistsVersion()
    {
        // Get the version to check if exists
        $version = $this->getVersion();
        if ($version === false) {
            $this->getDbDataset()->execSQL('insert into migration_version values(0)');
        }
    }
}
