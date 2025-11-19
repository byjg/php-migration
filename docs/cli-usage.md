---
sidebar_position: 4
title: CLI Usage
description: Command line interface guide for database migrations
---

# Command Line Interface Usage

The migration tool includes a built-in CLI with no external dependencies (pure PHP implementation).

## Installation

```bash
composer require "byjg/migration"
```

The CLI tool is automatically available at `vendor/bin/migrate` after installation.

## Commands

### version / status

Show the current database version and migration status.

```bash
vendor/bin/migrate version --connection <uri> [options]
# or
vendor/bin/migrate status --connection <uri> [options]
```

**Example:**
```bash
vendor/bin/migrate version -c mysql://user:pass@localhost/mydb
```

**Output:**
```
Current version: 5
Status: complete
```

### create / install

Create the migration version tracking table in your database. This should be run once before any migrations.

```bash
vendor/bin/migrate create --connection <uri> [options]
# or
vendor/bin/migrate install --connection <uri> [options]
```

**Example:**
```bash
vendor/bin/migrate create -c mysql://user:pass@localhost/mydb
```

### reset

Reset the database to the base schema (from `base.sql`) and optionally migrate to a specific version.

```bash
vendor/bin/migrate reset --connection <uri> [options]
```

**Examples:**
```bash
# Reset and migrate to latest version
vendor/bin/migrate reset -c mysql://user:pass@localhost/mydb -p ./migrations

# Reset and migrate to version 5
vendor/bin/migrate reset -c mysql://user:pass@localhost/mydb --version 5 -vv
```

### up

Migrate up to a specific version or to the latest available version.

```bash
vendor/bin/migrate up --connection <uri> [options]
```

**Examples:**
```bash
# Migrate to latest version
vendor/bin/migrate up -c mysql://user:pass@localhost/mydb

# Migrate to version 10
vendor/bin/migrate up -c mysql://user:pass@localhost/mydb --version 10

# Migrate with verbose output
vendor/bin/migrate up -c mysql://user:pass@localhost/mydb -vv
```

### down

Migrate down to a specific version or to version 0.

```bash
vendor/bin/migrate down --connection <uri> [options]
```

**Examples:**
```bash
# Migrate down to version 3
vendor/bin/migrate down -c mysql://user:pass@localhost/mydb --version 3

# Migrate down to version 0 (undo all migrations)
vendor/bin/migrate down -c mysql://user:pass@localhost/mydb --version 0
```

### update

Intelligently migrate up or down to reach a specific version. If no version is specified, migrates to the latest version.

```bash
vendor/bin/migrate update --connection <uri> [options]
```

**Examples:**
```bash
# Update to latest version
vendor/bin/migrate update -c mysql://user:pass@localhost/mydb

# Update to version 7 (will migrate up or down as needed)
vendor/bin/migrate update -c mysql://user:pass@localhost/mydb --version 7
```

## Options

### Connection Options

| Option | Short | Description | Required |
|--------|-------|-------------|----------|
| `--connection <uri>` | `-c` | Database connection URI | Yes (unless using env var) |
| `--path <path>` | `-p` | Path to migration folder | No (default: `.`) |
| `--table <name>` | `-t` | Migration version table name | No (default: `migration_version`) |

### Migration Options

| Option | Description |
|--------|-------------|
| `--version <n>` | `-u` | Target version for migration |
| `--force` | Force migration even if database is in partial state |
| `--no-transaction` | Disable transaction support |
| `--no-base` | Skip base.sql validation |

### Output Options

| Option | Description |
|--------|-------------|
| `-v` | Verbose output (level 1) |
| `-vv` | More verbose output (level 2) - shows connection details |
| `-vvv` | Very verbose output (level 3) - shows stack traces on errors |
| `--help` | `-h` | Show help message |

## Connection Strings

The `--connection` parameter accepts database URIs in the following formats:

```bash
# MySQL / MariaDB
mysql://username:password@hostname:port/database
mysql://username:password@hostname/database

# PostgreSQL
pgsql://username:password@hostname:port/database
pgsql://username:password@hostname/database

# SQLite
sqlite:///path/to/database.sqlite
sqlite:///absolute/path/to/database.db

# SQL Server (Linux - dblib)
dblib://username:password@hostname:port/database

# SQL Server (Windows - sqlsrv)
sqlsrv://username:password@hostname:port/database
```

## Environment Variables

The CLI automatically uses environment variables when command-line options are not provided:

### MIGRATION_CONNECTION

Set the database connection URI to avoid repeating it:

```bash
export MIGRATION_CONNECTION="mysql://user:pass@localhost/mydb"

# Now you can omit -c/--connection
vendor/bin/migrate version
vendor/bin/migrate up --version 5
```

### MIGRATION_PATH

Set the default migration folder path:

```bash
export MIGRATION_PATH="./database/migrations"

# Now you can omit -p/--path
vendor/bin/migrate up -c mysql://user:pass@localhost/mydb
```

### MIGRATION_TABLE

Set a custom migration version table name:

```bash
export MIGRATION_TABLE="custom_migration_version"

# Uses custom table name instead of default 'migration_version'
vendor/bin/migrate create -c mysql://user:pass@localhost/mydb
```

### DEBUG

Enable stack traces on errors:

```bash
DEBUG=true vendor/bin/migrate up -c mysql://user:pass@localhost/mydb
```

### Combined Usage

All environment variables can be used together:

```bash
export MIGRATION_CONNECTION="mysql://user:pass@localhost/mydb"
export MIGRATION_PATH="./database/migrations"
export MIGRATION_TABLE="app_migrations"

# All settings applied automatically
vendor/bin/migrate version
vendor/bin/migrate up
```

## Integration with Composer Scripts

You can add migration commands to your `composer.json` scripts section for easier execution:

### Basic Setup

Add migration scripts to your `composer.json`:

```json
{
  "scripts": {
    "migrate:version": "migrate version --connection ${MIGRATE_CONNECTION:-mysql://root@localhost/mydb}",
    "migrate:up": "migrate up --connection ${MIGRATE_CONNECTION:-mysql://root@localhost/mydb} --path ./database/migrations",
    "migrate:down": "migrate down --connection ${MIGRATE_CONNECTION:-mysql://root@localhost/mydb} --path ./database/migrations",
    "migrate:reset": "migrate reset --connection ${MIGRATE_CONNECTION:-mysql://root@localhost/mydb} --path ./database/migrations",
    "migrate:create": "migrate create --connection ${MIGRATE_CONNECTION:-mysql://root@localhost/mydb}"
  }
}
```

Then run migrations using:

```bash
composer migrate:version
composer migrate:up
composer migrate:reset
```

### Environment-Aware Setup

For different environments (development, staging, production):

```json
{
  "scripts": {
    "migrate:version": "@php bin/migrate-wrapper.php version",
    "migrate:up": "@php bin/migrate-wrapper.php up",
    "migrate:down": "@php bin/migrate-wrapper.php down",
    "migrate:reset": "@php bin/migrate-wrapper.php reset",
    "migrate:create": "@php bin/migrate-wrapper.php create"
  }
}
```

Create `bin/migrate-wrapper.php`:

```php
<?php
// Load environment-specific configuration
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$connection = getenv('MIGRATION_CONNECTION') ?: 'mysql://root@localhost/mydb';
$path = getenv('MIGRATION_PATH') ?: './database/migrations';
$table = getenv('MIGRATION_TABLE') ?: 'migration_version';
$command = $argv[1] ?? 'version';

$cmd = sprintf(
    '%s/migrate %s --connection %s --path %s --table %s',
    __DIR__ . '/../vendor/bin',
    escapeshellarg($command),
    escapeshellarg($connection),
    escapeshellarg($path),
    escapeshellarg($table)
);

passthru($cmd, $exitCode);
exit($exitCode);
```

### Advanced Setup with Parameters

For scripts that accept parameters:

```json
{
  "scripts": {
    "migrate": "migrate --connection ${MIGRATE_CONNECTION:-mysql://root@localhost/mydb} --path ./database/migrations",
    "migrate:version": "@migrate version",
    "migrate:up": "@migrate up",
    "migrate:to": "@migrate update"
  }
}
```

Usage:

```bash
# Check version
composer migrate:version

# Migrate up
composer migrate:up

# Migrate to specific version
composer migrate:to -- --version 5
```

Note: The `--` is required to pass options to the underlying command.

### CI/CD Pipeline Example

For continuous integration:

```json
{
  "scripts": {
    "migrate:check": [
      "@putenv MIGRATION_CONNECTION=mysql://ci_user:ci_pass@localhost/test_db",
      "@putenv MIGRATION_PATH=./database/migrations",
      "migrate version || migrate create",
      "migrate up -v"
    ],
    "test:integration": [
      "@migrate:check",
      "phpunit --testsuite integration"
    ]
  }
}
```

### With Verbosity

```json
{
  "scripts": {
    "migrate:up": "migrate up --connection ${MIGRATE_CONNECTION} --path ./database/migrations -vv",
    "migrate:reset": "migrate reset --connection ${MIGRATE_CONNECTION} --path ./database/migrations -vv"
  }
}
```

### Tips

1. **Use environment variables** for connection strings to avoid hardcoding credentials
2. **Add default values** using `${VAR:-default}` syntax for local development
3. **Create wrapper scripts** for complex setups with multiple environments
4. **Document your scripts** in your project's README
5. **Use `@` prefix** to reference other composer scripts

## Complete Examples

### Initial Setup

```bash
# 1. Create the version tracking table
vendor/bin/migrate create \
  --connection mysql://root:password@localhost/myapp \
  --path ./database/migrations

# 2. Check current version
vendor/bin/migrate version -c mysql://root:password@localhost/myapp

# 3. Migrate to latest version
vendor/bin/migrate up -c mysql://root:password@localhost/myapp -p ./database/migrations -vv
```

### Using Environment Variables

```bash
# Set connection once
export MIGRATION_CONNECTION="pgsql://postgres:password@localhost/myapp"
export MIGRATION_PATH="./migrations"

# Use for multiple commands without any arguments
vendor/bin/migrate create
vendor/bin/migrate reset
vendor/bin/migrate version
```

### Development Workflow

```bash
# Check current version
vendor/bin/migrate version -c sqlite:///./dev.db -p ./migrations

# Apply new migration
vendor/bin/migrate up -c sqlite:///./dev.db -p ./migrations -vv

# Rollback if needed
vendor/bin/migrate down -c sqlite:///./dev.db -p ./migrations --version 3
```

### Production Deployment

```bash
# Using environment variable for security
export MIGRATE_CONNECTION="mysql://app_user:${DB_PASSWORD}@db.example.com/production"

# Update to specific version with transaction support (default)
vendor/bin/migrate update --path /app/migrations --version 42 -v

# Check final version
vendor/bin/migrate version
```

### Troubleshooting

```bash
# Force migration if database is in partial state
vendor/bin/migrate up -c mysql://user:pass@localhost/mydb --force -vvv

# Reset completely and start fresh
vendor/bin/migrate reset -c mysql://user:pass@localhost/mydb -p ./migrations --version 0

# Check version without running migrations
vendor/bin/migrate version -c mysql://user:pass@localhost/mydb
```

## Migration Directory Structure

The CLI expects the following directory structure:

```text
<migration-path>
    |
    +-- base.sql           # Initial database schema (required by default)
    |
    +-- /migrations
            |
            +-- /up        # Scripts to upgrade versions
            |    |
            |    +-- 1.sql
            |    +-- 2-add-users.sql
            |    +-- 3.sql
            |
            +-- /down      # Scripts to downgrade versions (optional)
                 |
                 +-- 1.sql
                 +-- 2-add-users.sql
                 +-- 3.sql
```

Use `--no-base` flag if you don't have a `base.sql` file.

## Exit Codes

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | Error (general error, database error, migration error, etc.) |

## Comparison with Previous CLI

If you were using the separate `byjg/migration-cli` package, here are the main differences:

### Command Syntax Changes

| Old Command | New Command |
|-------------|-------------|
| `migrate create-database {uri}` | `migrate create -c {uri}` |
| `migrate version {uri}` | `migrate version -c {uri}` |
| `migrate update {uri}` | `migrate update -c {uri}` |
| `migrate up {uri} {version}` | `migrate up -c {uri} --version {version}` |
| `migrate down {uri} {version}` | `migrate down -c {uri} --version {version}` |
| `migrate reset {uri}` | `migrate reset -c {uri}` |

### Key Improvements

- **No Symfony Console dependency** - Pure PHP implementation
- **Consistent option syntax** - All options use `--option value` format
- **Better verbosity control** - Three levels: `-v`, `-vv`, `-vvv`
- **Environment variable support** - `MIGRATE_CONNECTION` for easier automation
- **Improved error messages** - Clearer error reporting
- **Help system** - Built-in help with `--help` flag
