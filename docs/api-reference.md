---
sidebar_position: 5
title: API Reference
description: Complete PHP API documentation for database migrations
---

# API Reference

## Migration Class

The main class for handling database migrations.

### Constructor

```php
public function __construct(
    UriInterface $uri,
    string $folder,
    bool $requiredBase = true,
    string $migrationTable = 'migration_version'
)
```

Parameters:
- `$uri`: Database connection URI
- `$folder`: Path to migration scripts
- `$requiredBase`: Whether base.sql is required
- `$migrationTable`: Name of migration tracking table

### Core Methods

#### Version Control

```php
// Create version tracking table
public function createVersion(): void

// Check if database is versioned
public function isDatabaseVersioned(): bool

// Get current version and status
// Returns: ['version' => int, 'status' => string]
public function getCurrentVersion(): array

// Update version table structure
public function updateTableVersion(): void
```

#### Migration Operations

```php
// Restore database and run all migrations
public function reset(?int $upVersion = null): void

// Migrate up to specific version
public function up(?int $version = null, bool $force = false): void

// Migrate down to specific version
public function down(?int $version = null, bool $force = false): void

// Smart migration up or down to target version
public function update(?int $version = null, bool $force = false): void
```

#### Configuration

```php
// Enable/disable transaction support
public function withTransactionEnabled(bool $enabled = true): static

// Add progress callback
public function addCallbackProgress(callable $callable): void

// Register database handler
public static function registerDatabase(string $class): void
```

#### Database Operations

```php
// Get database driver instance
public function getDbDriver(): DbDriverInterface

// Get database executor instance (run queries: execute, getScalar, getIterator)
public function getExecutor(): DatabaseExecutor

// Get database command instance
public function getDbCommand(): DatabaseInterface

// Create the database if it does not exist (do not use in production)
public function prepareEnvironment(): void

// Name of the migration tracking table
public function getMigrationTable(): string
```

### Progress Callback

The progress callback receives three parameters:

```php
function (
    string $action,        // Current action: 'reset' or 'migrate'
    int $currentVersion,   // Current version number
    array $fileInfo        // File information array
)
```

The `$fileInfo` array contains:
```php
[
    'file' => string,        // Full path to migration file
    'description' => string, // Migration description
    'exists' => bool,        // Whether file exists
    'checksum' => ?string,   // SHA1 hash of file contents (null if the file does not exist)
    'content' => ?string     // File contents (null if the file does not exist)
]
```

## Database Handlers

Custom database handlers must implement `DatabaseInterface`:

```php
interface DatabaseInterface
{
    public static function schema();                       // URI scheme(s) handled: string or string[]
    public static function prepareEnvironment(UriInterface $uri);
    public function createDatabase(): void;
    public function dropDatabase(): void;
    public function getVersion(): array;                   // ['version' => ..., 'status' => ...]
    public function updateVersionTable(): void;
    public function executeSql(string $sql): void;
    public function setVersion(int $version, MigrationStatus $status): void;
    public function createVersion(): void;
    public function isDatabaseVersioned(): bool;
    public function getDbDriver(): DbDriverInterface;
    public function getExecutor(): DatabaseExecutor;
    public function getMigrationTable(): string;
    public function supportsTransaction(): bool;
}
```

Extending `AbstractDatabase` provides most of them.

### Built-in Handlers

- `MySqlDatabase`: MySQL/MariaDB (`mysql://`, `mariadb://`)
- `PgsqlDatabase`: PostgreSQL (`pgsql://`, `postgres://`, `postgresql://`)
- `SqliteDatabase`: SQLite (`sqlite://`)
- `DblibDatabase`: Microsoft SQL Server (`dblib://`, `sqlsrv://`)

## Examples

### Basic Migration

```php
$uri = new \ByJG\Util\Uri('mysql://user:pass@localhost/database');
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

$migration = new \ByJG\DbMigration\Migration($uri, '/migrations');
$migration->update();
```

### With Progress Tracking

```php
$migration->addCallbackProgress(function ($action, $version, $fileInfo) {
    echo sprintf(
        "Executing %s to version %d: %s\n",
        $action,
        $version,
        $fileInfo['description']
    );
});
```

### With Transaction Support

:::note Transaction Support
Transaction support is not available for MySQL as it doesn't support DDL commands inside transactions. The setting will be silently ignored for MySQL databases.
:::

```php
$migration
    ->withTransactionEnabled(true)
    ->update();
```

### Custom Version Table

```php
$migration = new \ByJG\DbMigration\Migration(
    $uri,
    '/migrations',
    true,
    'custom_version_table'
);
``` 