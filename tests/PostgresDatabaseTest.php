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

    /**
     * The URI scheme of the connection; PostgresSchemeAliasTest runs the same tests with another.
     */
    protected string $scheme = 'pgsql';

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

        $uri = "{$this->scheme}://postgres:{$password}@{$host}/migratedatabase";

        Migration::registerDatabase(PgsqlDatabase::class);

        $this->migrate = new Migration(new Uri($uri), __DIR__ . '/fixtures/postgres', true, $this->migrationTable);
        parent::setUp();
    }

    public function testDropDatabaseDropsEveryObjectTheMigrationsCreate(): void
    {
        $this->migrate->reset();
        $executor = $this->migrate->getExecutor();
        $executor->execute("create type mood as enum ('ok', 'sad')");
        $executor->execute("create type pair as (a int, b int)");
        $executor->execute("create domain positive as integer check (value > 0)");
        $executor->execute("create sequence standalone_sequence");
        $executor->execute("create view users_view as select * from users");
        $executor->execute("create materialized view users_count as select count(*) as total from users");
        $executor->execute("create function touch() returns trigger as $$ begin return new; end; $$ language plpgsql");

        $this->migrate->getDbCommand()->dropDatabase();

        $left = fn (string $sql) => (int)$executor->getScalar($sql);
        $this->assertSame(0, $left("select count(*) from pg_class c join pg_namespace n on n.oid = c.relnamespace where n.nspname = 'public' and c.relkind in ('r', 'v', 'm', 'S')"), 'tables, views, sequences');
        $this->assertSame(0, $left("select count(*) from pg_proc p join pg_namespace n on n.oid = p.pronamespace where n.nspname = 'public'"), 'functions');
        $this->assertSame(0, $left("select count(*) from pg_type t join pg_namespace n on n.oid = t.typnamespace where n.nspname = 'public' and t.typtype in ('e', 'd', 'c', 'r')"), 'types and domains');

        // The function left behind used to make this fail on the next CREATE FUNCTION
        $executor->execute("create function touch() returns trigger as $$ begin return new; end; $$ language plpgsql");
        $this->migrate->reset();
        $this->assertSame(2, $left('select count(*) from users'));
    }
}
