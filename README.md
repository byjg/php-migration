# Database Migrations
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/migration/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/migration/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/571cb412-7018-4938-a4e5-0f9ce44956d7/mini.png)](https://insight.sensiolabs.com/projects/571cb412-7018-4938-a4e5-0f9ce44956d7)
[![Build Status](https://travis-ci.org/byjg/migration.svg?branch=master)](https://travis-ci.org/byjg/migration)

Simple library in PHP for database version control. Supports Sqlite, MySql, Sql Server and Postgres.

Database Migration is a set of commands for upgrade or downgrade a database.
This library uses only SQL commands.

## Introduction

The basic usage is 

- Create a connection a ConnectionManagement object. For more information see the "byjg/anydataset" component
- Create a Migration object with this connection and the folder where the scripts sql are located. 
- Use the proper command for "reset", "up" or "down" the migrations scripts. 

See an example:

```php
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');
$migration = new Migration($connectionUri, '.');

// Restore the database using the "base.sql" script and run ALL existing scripts for up the database version
// and run the up() method to maintain the database updated;
$migration->reset();

// Run ALL existing scripts for up the database version from the current version to the last version; 
$migration->up();
```

The Migration object controls the database version.  

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

### Multi Development environment 

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

## Running in the command line

Migration library creates the 'migrate' script. It has the follow syntax:

```
Usage:
  command [options] [arguments]

Available commands:
  create   Create the directory structure FROM a pre-existing database
  install  Install or upgrade the migrate version in a existing database
  down   Migrate down the database version.
  reset  Create a fresh new database
  up     Migrate Up the database version
  version  Get the current database version

Arguments:
  connection            The connection string. Ex. mysql://root:password@server/database [default: false]

Example:
  migrate reset mysql://root:password@server/database
  migrate up mysql://root:password@server/database
  migrate down mysql://root:password@server/database
  migrate up --up-to=10 --path=/somepath mysql://root:password@server/database
  migrate down --up-to=3 --path=/somepath mysql://root:password@server/database
```

## Supported databases:

* Sqlite
* Mysql / MariaDB
* Postgres
* SqlServer

## Installing Globally

```bash
composer global require 'byjg/migration=2.0.*'
sudo ln -s $HOME/.composer/vendor/bin/migrate /usr/local/bin
```

## Unit Tests

This library has integrated tests and need to be setup for each database you want to test. 

Basiclly you have the follow tests:

```
phpunit tests/SqliteDatabaseTest.php
phpunit tests/MysqlDatabaseTest.php
phpunit tests/PostgresDatabaseTest.php
phpunit tests/SqlServerDatabaseTest.php 
```