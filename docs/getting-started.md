---
sidebar_position: 1
title: Getting Started
description: Get started with PHP Database Migration - a framework-agnostic database versioning tool using pure SQL
---

# Getting Started with PHP Database Migration

## Why Pure SQL Commands?

Most frameworks use programming statements for database versioning instead of pure SQL. While framework-specific approaches have advantages like:

* Framework commands for complex tasks
* Database-agnostic code
* Built-in framework integration

However, in real-world projects, developers often use tools like MySQL Workbench or DataGrip to make database changes and then spend time translating SQL to framework code. This library embraces SQL-first approach, allowing you to use your database tools' native SQL output directly.

## Installation

### As a Library

```bash
composer require "byjg/migration"
```

### As a CLI Tool

```bash
composer require "byjg/migration-cli"
```

:::tip
For CLI usage, see the [CLI documentation](cli-usage.md) for detailed commands and options.
:::

## Basic Usage

1. Create your migration directory structure:
```text
<root dir>
    |
    +-- base.sql           # Initial database schema
    |
    +-- /migrations
            |
            +-- /up        # Scripts to upgrade version
            |    |
            |    +-- 00001.sql
            |    +-- 00002.sql
            |
            +-- /down      # Scripts to downgrade version (optional)
                 |
                 +-- 00001.sql
                 +-- 00000.sql
```

2. Initialize in your code:

```php
<?php
// Create database connection
$connectionUri = new \ByJG\Util\Uri('mysql://user:pass@localhost/database');

// Register database handler
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Create migration instance
$migration = new \ByJG\DbMigration\Migration($connectionUri, '/path/to/migrations');

// Optional: Add progress callback
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Create version table (first time only)
$migration->createVersion();

// Run migrations
$migration->update();
```

## Next Steps

- Learn about [Migration Scripts](migration-scripts.md)
- See [Database-specific setup](database-setup.md)
- Explore the [API Reference](api-reference.md)
- Check [CLI Usage](cli-usage.md) 