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

- PHP requirement is now `>=8.3 <8.7` (see Requirements below)
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

## Requirements

- PHP 8.3, 8.4, 8.5 and 8.6 are now supported: `"php": ">=8.3 <8.7"`.
  The previous `<8.6` upper bound excluded PHP 8.6, since `<8.6` is exclusive.

### ByJG dependencies

- `byjg/anydataset-db` is now `^7.0`.

While 7.0 is unreleased these resolve to `7.0.x-dev` from each component's
`7.0` branch, via `minimum-stability: dev` with `prefer-stable: true`.

## Toolchain

- PHPUnit updated to `^12.5`.
- Psalm moved out of `require-dev` into its own manifest, `tools/psalm/composer.json`.

  Psalm enumerates the PHP versions it supports and no published release lists
  8.6. As a dev dependency it made `composer install` fail on the 8.6 build job
  before any test ran. It now installs separately, only for the Psalm job.

  `composer psalm` still works — it bootstraps the tool and runs it.

- PHPUnit 13 is deliberately **not** used. It requires PHP `>=8.4.1`, breaking the
  8.3 floor, and needs `sebastian/diff ^9.0`, which stable Psalm 6.16.1 rejects —
  a combination that silently resolves Psalm to an unreleased `6.x-dev` branch.

## Continuous Integration

- The build matrix now includes PHP 8.6.
- The Psalm job runs on PHP 8.5 and installs Psalm from `tools/psalm`.

## Housekeeping

- `phpunit.xml.dist` renamed to `phpunit.xml`.
