<?php

namespace ByJG\DbMigration\Database;

use Psr\Http\Message\UriInterface;

interface DatabaseInterface
{
    public static function schema();

    public static function prepareEnvironment(UriInterface $dbDriver);

    public function createDatabase();

    public function dropDatabase();

    /**
     * @return array
     * @throws \ByJG\DbMigration\Exception\DatabaseNotVersionedException
     * @throws \ByJG\DbMigration\Exception\OldVersionSchemaException
     */
    public function getVersion();

    public function updateVersionTable();

    public function executeSql($sql);

    public function setVersion($version, $status);

    public function createVersion();

    public function isDatabaseVersioned();

    public function getDbDriver();

    public function getMigrationTable();
}
