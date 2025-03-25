<?php

namespace Tests;

use ByJG\DbMigration\Database\PgsqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * @requires extension pdo_pgsql
 */
#[RequiresPhpExtension('pdo_pgsql')]
class PostgresDatabaseTest extends BaseDatabase
{
    /**
     * @var Migration|null
     */
    protected ?Migration $migrate = null;

    #[\Override]
    public function setUp(): void
    {
        $host = getenv('PSQL_TEST_HOST');
        if (empty($host)) {
            $host = "127.0.0.1";
        }
        $password = getenv('PSQL_PASSWORD');
        if (empty($password)) {
            $password = 'password';
        }
        if ($password == '.') {
            $password = "";
        }

        $uri = "pgsql://postgres:{$password}@{$host}/migratedatabase";

        Migration::registerDatabase(PgsqlDatabase::class);

        $this->migrate = new Migration(new Uri($uri), __DIR__ . '/../example/postgres', true, $this->migrationTable);
        parent::setUp();
    }
}
