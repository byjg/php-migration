---
sidebar_key: migration
sidebar_category: data-access
tags: [php, databases, cli]
---

# Database Migration

Version your database with plain SQL files, from any framework or none.

[![Sponsor](https://img.shields.io/badge/Sponsor-%23ea4aaa?logo=githubsponsors&logoColor=white&labelColor=0d1117)](https://github.com/sponsors/byjg)
[![Build Status](https://github.com/byjg/php-migration/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-migration/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](https://opensource.byjg.com)
[![Install MCP Server](https://img.shields.io/badge/Install-MCP_Server-8A2BE2?logo=modelcontextprotocol&logoColor=white)](https://opensource.byjg.com/docs/ai/mcpserver-byjg-docs/)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-migration/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-migration.svg)](https://opensource.byjg.com/license/)
[![GitHub release](https://img.shields.io/github/release/byjg/php-migration.svg)](https://github.com/byjg/php-migration/releases/)

## Why

Most migration tools are part of a framework and make you write migrations in its PHP
schema builder. The migrations then only run inside that framework, and the SQL your
database actually executes is hidden behind it.

Here the schema starts from a `base.sql`, and each migration is a numbered `.sql` file in
`migrations/up`, with an optional script in `migrations/down` to go back:

```
base.sql
migrations/up/00001-add-email.sql      -- @description: Add the email column
migrations/up/00002-create-orders.sql
```

- **Plain SQL.** You write exactly what the database runs, and any tool, PHP or not, can
  read it.
- **Framework independent.** Use the `migrate` CLI or the library, in any project.
- **A failed migration is not forgotten.** The database is marked *partial up* or
  *partial down*, and further migrations refuse to run until you fix it (or `--force`).

## Key Features

- **Pure SQL Migrations** - Use native SQL commands instead of framework-specific code
- **Multiple Database Support** - Works with MySQL, PostgreSQL, SQLite, and SQL Server
- **Framework Independent** - Can be used with any PHP framework or standalone
- **CLI & Library Support** - Use as a command-line tool or integrate into your PHP application
- **Transaction Support** - Ensures database consistency (where supported)
- **Multi-Developer Friendly** - Supports parallel development with -dev suffixed versions

## Quick Start

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

## Installation

```bash
composer require "byjg/migration"
```

## Running Tests

```bash
docker compose up -d
vendor/bin/phpunit
```

----
[Open source ByJG](https://opensource.byjg.com)