<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use Psr\Http\Message\UriInterface;

interface DatabaseInterface
{
    public static function schema();

    public static function prepareEnvironment(UriInterface $dbDriver);

    public function createDatabase(): void;

    public function dropDatabase(): void;

    /**
     * @return array
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    public function getVersion(): array;

    public function updateVersionTable(): void;

    public function executeSql(string $sql): void;

    public function setVersion(string $version, string $status): void;

    public function createVersion(): void;

    public function isDatabaseVersioned(): bool;

    public function getDbDriver(): DbDriverInterface;

    public function getMigrationTable(): string;

    public function supportsTransaction(): bool;
}
