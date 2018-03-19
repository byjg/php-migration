# Database Migrations
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/migration/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/migration/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/571cb412-7018-4938-a4e5-0f9ce44956d7/mini.png)](https://insight.sensiolabs.com/projects/571cb412-7018-4938-a4e5-0f9ce44956d7)
[![Build Status](https://travis-ci.org/byjg/migration.svg?branch=master)](https://travis-ci.org/byjg/migration)

Simple library in PHP for database version control. Supports Sqlite, MySql, Sql Server and Postgres.

Database Migration is a set of commands for upgrade or downgrade a database.
This library uses only SQL commands.

## Installing

```
composer require 'byjg/migration=2.0.*'
```

## Supported databases:

 * Sqlite
 * Mysql / MariaDB
 * Postgres
 * SqlServer

## How It Works?

The Database Migration uses PURE SQL to manage the database versioning. 
In order to get working you need to:

 - Create the SQL Scripts
 - Manage using Command Line or the API.  

### The SQL Scripts

The scripts are divided in three set of scripts:

- The BASE script contains ALL sql commands for create a fresh database; 
- The UP scripts contain all sql migration commands for "up" the database version;
- The DOWN scripts contain all sql migration commands for "down" or revert the database version;

The directory scripts is :

```
 <root dir>
     |
     +-- base.sql
     |
     +-- /migrations
              |
              +-- /up
                   |
                   +-- 00001.sql
                   +-- 00002.sql
              +-- /down
                   |
                   +-- 00000.sql
                   +-- 00001.sql
``` 

 - "base.sql" is the base script
 - "up" folder contains the scripts for migrate up the version. 
    For example: 00002.sql is the script for move the database from version '1' to '2'.
 - "down" folder contains the scripts for migrate down the version. 
   For example: 00001.sql is the script for move the database from version '2' to '1'.
   The "down" folder is optional. 

**Multi Development environment** 

If you work with multiple developers and multiple branches it is to difficult to determine what is the next number.

In that case you have the suffix "-dev" after the version number. 

See the scenario:

 - Developer 1 create a branch and the most recent version in e.g. 42.
 - Developer 2 create a branch at the same time and have the same database version number.

In both case the developers will create a file called 43-dev.sql. Both developers will migrate UP and DOWN with
no problem and your local version will be 43. 

But developer 1 merged your changes and created a final version 43.sql (`git mv 43-dev.sql 43.sql`). If the developer 2
update your local branch he will have a file 43.sql (from dev 1) and your file 43-dev.sql. 
If he is try to migrate UP or DOWN
the migration script will down and alert him there a TWO versions 43. In that case, developer 2 will have to update your
file do 44-dev.sql and continue to work until merge your changes and generate a final version. 

### Running in the command line

Migration library creates the 'migrate' script. It has the follow syntax:

```
Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  create   Create the directory structure FROM a pre-existing database
  down     Migrate down the database version.
  help     Displays help for a command
  install  Install or upgrade the migrate version in a existing database
  list     Lists commands
  reset    Create a fresh new database
  up       Migrate Up the database version
  update   Migrate Up or Down the database version based on the current database version and the migration scripts available
  version  Get the current database version
```

#### Commands

##### Basic Usage

The basic usage is:

```text
migrate <COMMAND> --path=<scripts> uri://connection
```

The `--path` specify where the base.sql and migrate scripts are located. 
If you omitted the `--path` it will assume the current directory. You can also
set the `MIGRATE_PATH` environment variable with the base path 

The uri://connection is the uri that represents the connection to the database. 
You can see [here](https://github.com/byjg/anydataset#connection-based-on-uri)
to know more about the connection string.

You can omit the uri parameter if you define it in the 
`MIGRATE_CONNECTION` environment variable

```bash
export MIGRATE_CONNECTION=sqlite:///path/to/my.db
```
  
##### Command: create

Create a empty directory structure with base.sql and migrations/up and migrations/down for migrations. This is
useful for create from scratch a migration scheme.

Ex.

```bash
migrate create /path/to/sql 
```

##### Command: install 

If you already have a database but it is not controlled by the migration system you can use this method for 
install the required tables for migration.

```bash
migrate install mysql://server/database
```

##### Command: update

Will apply all necessary migrations to keep your database updated.

```bash
migrate update mysql://server/database
```

Update command can choose if up or down your database depending on your current database version.
You can also specify a version: 

```bash
migrate update --up-to=34
``` 

##### Command: reset

Creates/replace a database with the "base.sql" and apply ALL migrations

```bash
migrate reset            # reset the database and apply all migrations scripts.
migrate reset --up-to=5  # reset the database and apply the migration from the 
                         # start up to the version 5.
migrate reset --yes      # reset the database without ask anything. Be careful!!
```

**Note on reset:** You can disable the reset command by setting the environment variable 
`MIGRATE_DISABLE_RESET` to true:

```bash
export MIGRATE_DISABLE_RESET=true
```

### Using the API

The basic usage is 

- Create a connection a ConnectionManagement object. For more information see the "byjg/anydataset" component
- Create a Migration object with this connection and the folder where the scripts sql are located. 
- Use the proper command for "reset", "up" or "down" the migrations scripts. 

See an example:

```php
<?php
// Create the Connection URI
// See more: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Create the Migration instance
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Register the Database or Databases can handle that URI:
$migration->registerDatabase('mysql', \ByJG\DbMigration\Database\MySqlDatabase::class);
$migration->registerDatabase('maria', \ByJG\DbMigration\Database\MySqlDatabase::class);

// Restore the database using the "base.sql" script
// and run ALL existing scripts for up the database version to the latest version
$migration->reset();

// Run ALL existing scripts for up the database version
// from the current version to the last version; 
$migration->up();
```

The Migration object controls the database version.  



## Unit Tests

This library has integrated tests and need to be setup for each database you want to test. 

Basiclly you have the follow tests:

```
phpunit tests/SqliteDatabaseTest.php
phpunit tests/MysqlDatabaseTest.php
phpunit tests/PostgresDatabaseTest.php
phpunit tests/SqlServerDatabaseTest.php 
```
