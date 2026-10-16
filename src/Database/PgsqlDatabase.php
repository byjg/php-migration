<?php

namespace ByJG\DbMigration\Database;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\AnyDataset\Db\Factory;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\Util\Uri;
use Psr\Http\Message\UriInterface;

class PgsqlDatabase extends AbstractDatabase
{
    /**
     * The same schemes byjg/anydataset-db accepts for PostgreSQL.
     */
    #[\Override]
    public static function schema(): array
    {
        return ['pgsql', 'postgres', 'postgresql'];
    }

    #[\Override]
    public static function prepareEnvironment(UriInterface|Uri $uri): void
    {
        $uriInstance = $uri instanceof Uri ? $uri : new Uri($uri->__toString());
        $database = static::getDatabaseName($uriInstance);
        $executor = static::getExecutorWithoutDatabase($uri, 'postgres');
        static::createDatabaseIfNotExists($executor, $database);
    }

    /**
     * @param DatabaseExecutor $executor
     * @param $database
     */
    protected static function createDatabaseIfNotExists(DatabaseExecutor $executor, string $database): void
    {
        $currentDbName = $executor->getScalar(
            "SELECT datname FROM pg_catalog.pg_database WHERE lower(datname) = lower(:dbname)",
            ['dbname' => $database]
        );

        if (empty($currentDbName)) {
            $executor->execute("CREATE DATABASE $database WITH encoding=\"UTF8\";");
        }
    }

    #[\Override]
    public function createDatabase(): void
    {
        $database = static::getDatabaseName($this->getDbDriver()->getUri());
        static::createDatabaseIfNotExists($this->getExecutor(), $database);
    }

    #[\Override]
    /**
     * Drops what the migrations may have created in the public schema, not only the tables:
     * views, sequences, functions and procedures, domains and types. A function left behind made
     * the next reset fail on its CREATE FUNCTION. Objects that belong to an extension are left to
     * it (PostgreSQL does not drop them on their own).
     */
    public function dropDatabase(): void
    {
        $notFromAnExtension = "not exists (select 1 from pg_depend d where d.objid = %s and d.deptype = 'e')";
        $iterator = $this->getExecutor()->getIterator(
            "select command from (
                select 1 as position, format('drop materialized view if exists %I.%I cascade', schemaname, matviewname) as command
                  from pg_matviews where schemaname = 'public'
                union all
                select 2, format('drop view if exists %I.%I cascade', schemaname, viewname)
                  from pg_views where schemaname = 'public'
                union all
                select 3, format('drop table if exists %I.%I cascade', schemaname, tablename)
                  from pg_tables where schemaname = 'public'
                union all
                select 4, format('drop sequence if exists %I.%I cascade', sequence_schema, sequence_name)
                  from information_schema.sequences where sequence_schema = 'public'
                union all
                select 5, format('drop routine if exists %I.%I(%s) cascade', n.nspname, p.proname, pg_get_function_identity_arguments(p.oid))
                  from pg_proc p join pg_namespace n on n.oid = p.pronamespace
                 where n.nspname = 'public' and p.prokind in ('f', 'p') and " . sprintf($notFromAnExtension, 'p.oid') . "
                union all
                select 6, format('drop domain if exists %I.%I cascade', n.nspname, t.typname)
                  from pg_type t join pg_namespace n on n.oid = t.typnamespace
                 where n.nspname = 'public' and t.typtype = 'd' and " . sprintf($notFromAnExtension, 't.oid') . "
                union all
                select 7, format('drop type if exists %I.%I cascade', n.nspname, t.typname)
                  from pg_type t join pg_namespace n on n.oid = t.typnamespace
                 where n.nspname = 'public'
                   and (t.typtype in ('e', 'r') or (t.typtype = 'c' and (select c.relkind from pg_class c where c.oid = t.typrelid) = 'c'))
                   and " . sprintf($notFromAnExtension, 't.oid') . "
            ) commands order by position"
        );
        foreach ($iterator as $singleRow) {
            $this->getExecutor()->execute($singleRow->get('command'));
        }
    }

    /**
     * @throws DatabaseNotVersionedException
     * @throws OldVersionSchemaException
     */
    #[\Override]
    public function createVersion(): void
    {
        $this->getExecutor()->execute('CREATE TABLE IF NOT EXISTS ' . $this->getMigrationTable() . ' (version int, status varchar(20), PRIMARY KEY (version))');
        $this->checkExistsVersion();
    }

    #[\Override]
    public function executeSql(string $sql): void
    {
        $statements = preg_split("/;(\r\n|\r|\n)/", $sql);

        if ($statements === false) {
            $statements = [$sql];
        }

        foreach ($statements as $sql) {
            $this->executeSqlInternal($sql);
        }
    }

    protected function executeSqlInternal(string $sql): void
    {
        if (empty(trim($sql))) {
            return;
        }
        $this->getExecutor()->execute($sql);
    }

    #[\Override]
    public function isDatabaseVersioned(): bool
    {
        return $this->isTableExists('public', $this->getMigrationTable());
    }
}
