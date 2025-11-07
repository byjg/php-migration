---
sidebar_position: 4
title: CLI Usage
description: Command line interface guide for database migrations
---

# Command Line Interface Usage

The CLI tool is available as a separate package: `byjg/migration-cli`

## Installation

```bash
composer require "byjg/migration-cli"
```

## Basic Commands

### Create Version Table

```bash
vendor/bin/migrate create-database {connection}
```

### Show Current Version

```bash
vendor/bin/migrate version {connection}
```

### Update Database

```bash
# Update to latest version
vendor/bin/migrate update {connection}

# Update to specific version
vendor/bin/migrate up {connection} {version}

# Downgrade to specific version
vendor/bin/migrate down {connection} {version}
```

### Reset Database

```bash
vendor/bin/migrate reset {connection}
```

## Connection String

The `{connection}` parameter should be a valid URI:

```bash
# MySQL
mysql://username:password@hostname:port/database

# PostgreSQL
pgsql://username:password@hostname:port/database

# SQLite
sqlite:///path/to/database.sqlite

# SQL Server (Linux)
dblib://username:password@hostname:port/database

# SQL Server (Windows)
sqlsrv://username:password@hostname:port/database
```

## Environment Variables

You can use environment variables in your connection string:

```bash
# Set environment variables
export DB_USER=myuser
export DB_PASS=mypass
export DB_HOST=localhost
export DB_NAME=mydb

# Use in connection string
vendor/bin/migrate update "mysql://${DB_USER}:${DB_PASS}@${DB_HOST}/${DB_NAME}"
```

## Migration Path

By default, the CLI looks for migrations in the current directory. Use `--path` to specify a different location:

```bash
vendor/bin/migrate update {connection} --path=/path/to/migrations
```

## Examples

### Basic Usage

```bash
# Create version table
vendor/bin/migrate create-database "mysql://root:pass@localhost/mydb"

# Show current version
vendor/bin/migrate version "mysql://root:pass@localhost/mydb"

# Update to latest version
vendor/bin/migrate update "mysql://root:pass@localhost/mydb"
```

### With Custom Path

```bash
vendor/bin/migrate update "mysql://root:pass@localhost/mydb" \
    --path=/app/migrations
```

### Reset and Update

```bash
# Reset database and migrate to latest version
vendor/bin/migrate reset "mysql://root:pass@localhost/mydb"

# Reset and migrate to specific version
vendor/bin/migrate reset "mysql://root:pass@localhost/mydb" --up-to=5
```

## Exit Codes

- 0: Success
- 1: General error
- 2: Database error
- 3: Migration error
- 4: Invalid arguments

## Environment Variables

The CLI tool also supports configuration via environment variables:

```bash
# Migration table name
export MIGRATION_VERSION=custom_migration_table

# Database credentials
export MYSQL_TEST_HOST=localhost
export MYSQL_PASSWORD=password
export PSQL_TEST_HOST=localhost
export PSQL_PASSWORD=password
# etc...
``` 