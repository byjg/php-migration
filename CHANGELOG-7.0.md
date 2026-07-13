# Changelog - Version 7.0

## Overview

Version 7.0 upgrades the library to `byjg/anydataset-db` 7.0, which introduced a major API redesign: query execution moved from the database driver to the new `DatabaseExecutor` class. Starting with this release, the migration library version is aligned with the `byjg/anydataset-db` major version.

## New Features

### DatabaseExecutor Support
- **New `getExecutor()` method**: Available on `Migration`, `DatabaseInterface`, and `AbstractDatabase`, returning a `ByJG\AnyDataset\Db\DatabaseExecutor` instance
- **Single entry point for queries**: Use the executor to run queries against the connection managed by the migration library:

```php
$migration = new Migration($uri, $path);

// Before (6.x)
$migration->getDbDriver()->execute("insert into ...");
$value = $migration->getDbDriver()->getScalar("select ...");

// After (7.x)
$migration->getExecutor()->execute("insert into ...");
$value = $migration->getExecutor()->getScalar("select ...");
```

### Development Environment
- SQL Server image pinned to `mcr.microsoft.com/mssql/server:2022-latest` with `MSSQL_SA_PASSWORD` environment variable
- Removed obsolete `version` key from `docker-compose.yml`

## Breaking Changes

| Component | Before (6.x) | After (7.x) | Description |
|-----------|-------------|------------|-------------|
| **byjg/anydataset-db** | `^6.0` | `^7.0` | Query methods (`execute`, `getScalar`, `getIterator`) were removed from `DbDriverInterface` and moved to `DatabaseExecutor` |
| **Query execution** | `$migration->getDbDriver()->execute($sql)` | `$migration->getExecutor()->execute($sql)` | The driver returned by `getDbDriver()` no longer executes queries; it still handles connection and transactions |
| **DatabaseInterface** | — | `getExecutor(): DatabaseExecutor` | New required method. Custom database handlers must implement it (or extend `AbstractDatabase`, which provides it) |
| **AbstractDatabase (protected)** | `getDbDriverWithoutDatabase(): DbDriverInterface` | `getExecutorWithoutDatabase(): DatabaseExecutor` | Renamed static helper used by `prepareEnvironment()` implementations |
| **PgsqlDatabase (protected)** | `createDatabaseIfNotExists(DbDriverInterface $dbDriver, ...)` | `createDatabaseIfNotExists(DatabaseExecutor $executor, ...)` | Signature changed for subclasses overriding this method |
| **PHPUnit** | `^10.5\|^11.5` | `^12.5` | Dev dependency upgrade |

### Unchanged

- PHP requirement remains `>=8.3 <8.6`
- `getDbDriver()` is still available on `Migration` and `DatabaseInterface` for connection and transaction control (`beginTransaction`, `commitTransaction`, `rollbackTransaction`)
- Migration scripts, folder structure, CLI usage, and connection strings are unaffected

## Upgrade Path from 6.x to 7.x

### Step 1: Update composer.json

```json
{
  "require": {
    "byjg/migration": "^7.0"
  }
}
```

```bash
composer update byjg/migration
```

This will automatically update `byjg/anydataset-db` to version 7.0 as well.

### Step 2: Replace Direct Driver Query Calls (If Applicable)

If you call query methods on the driver returned by `getDbDriver()`, switch to the executor:

```php
// Before
$migration->getDbDriver()->getIterator("select * from users");

// After
$migration->getExecutor()->getIterator("select * from users");
```

Transaction calls on the driver do not need to change.

### Step 3: Update Custom Database Handlers (If Applicable)

If you implemented `DatabaseInterface` directly, add the new method:

```php
public function getExecutor(): DatabaseExecutor;
```

If you extend `AbstractDatabase`, the method is inherited. Subclasses using the static helper must rename `getDbDriverWithoutDatabase()` calls to `getExecutorWithoutDatabase()` (note it now returns a `DatabaseExecutor`).

### Step 4: Test Your Migrations

```bash
vendor/bin/phpunit
vendor/bin/migrate version -c <your-connection-string>
```

## Support

For issues or questions about this release:
- GitHub Issues: https://github.com/byjg/php-migration/issues
- Documentation: See the `docs/` directory

## License

This project continues to be licensed under the MIT License.
