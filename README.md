# Database Migration

[![Build Status](https://github.com/byjg/php-migration/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-migration/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-migration/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-migration.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/php-migration.svg)](https://github.com/byjg/php-migration/releases/)

A simple, framework-agnostic database migration tool that uses pure SQL commands for versioning your database.

## Key Features

- **Pure SQL Migrations** - Use native SQL commands instead of framework-specific code
- **Multiple Database Support** - Works with MySQL, PostgreSQL, SQLite, and SQL Server
- **Framework Independent** - Can be used with any PHP framework or standalone
- **CLI & Library Support** - Use as a command-line tool or integrate into your PHP application
- **Transaction Support** - Ensures database consistency (where supported)
- **Multi-Developer Friendly** - Supports parallel development with -dev suffixed versions

## Quick Start

### Installation

```bash
composer require "byjg/migration"
```

### CLI Usage

The package includes a built-in CLI tool:

```bash
# Show current database version
vendor/bin/migrate version --connection mysql://user:pass@localhost/database

# Update database to latest version
vendor/bin/migrate update --connection mysql://user:pass@localhost/database --path ./migrations

# Reset database and run all migrations
vendor/bin/migrate reset -c mysql://user:pass@localhost/database -p ./migrations
```

See [CLI Usage](docs/cli-usage.md) for complete documentation.

### Library Usage

```php
<?php
$connectionUri = new \ByJG\Util\Uri('mysql://user:pass@localhost/database');
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');
$migration->update();
```

## Documentation

For detailed documentation, please visit:

- [Getting Started](docs/getting-started.md) - Basic concepts and setup
- [Database Setup](docs/database-setup.md) - Configure your database connection
- [Migration Scripts](docs/migration-scripts.md) - Writing and organizing migrations
- [CLI Usage](docs/cli-usage.md) - Command line interface guide
- [API Reference](docs/api-reference.md) - Complete API documentation

## Supported Databases

- MySQL/MariaDB
- PostgreSQL
- SQLite
- SQL Server (Windows and Linux)

For connection strings and specific database setup, see our [database documentation](docs/database-setup.md).

## Related Projects

- [Micro ORM](https://github.com/byjg/micro-orm)
- [Anydataset](https://github.com/byjg/anydataset)
- [PHP Rest Template](https://github.com/byjg/php-rest-template)

## Dependencies

```mermaid
flowchart TD
    byjg/migration --> byjg/anydataset-db
    byjg/migration --> ext-pdo
```

----
[Open source ByJG](http://opensource.byjg.com)