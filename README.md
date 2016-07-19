# Database Migrations
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/migration/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/migration/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/571cb412-7018-4938-a4e5-0f9ce44956d7/mini.png)](https://insight.sensiolabs.com/projects/571cb412-7018-4938-a4e5-0f9ce44956d7)

A micro framework in PHP for managing a set of database migrations using pure Sql.

Database Migration is a set of commands for upgrade or downgrade a database.
This library uses only SQL commands.

## Introduction

The basic usage is 

- Create a connection a ConnectionManagement object. For more information see the "byjg/anydataset" component
- Create a Migration object with this connection and the folder where the scripts sql are located. 
- Use the proper command for "reset", "up" or "down" the migrations scripts. 

See an example:

```php
$connection = new ConnectionManagement('mysql://migrateuser:migratepwd@localhost/migratedatabase');
$migration = new Migration($connection, '.');

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


## Running in the command line

Migration library creates the 'migrate' script. It has the follow syntax:

```
Usage:
  command [options] [arguments]

Available commands:
  down   Migrate down the database version.
  reset  Create a fresh new database
  up     Migrate Up the database version

Arguments:
  connection            The connection string. Ex. mysql://root:password@server/database [default: false]

Example:
  migrate reset mysql://root:password@server/database
  migrate up mysql://root:password@server/database
  migrate down mysql://root:password@server/database
  migrate up --up-to=10 --path=/somepath mysql://root:password@server/database
  migrate down --up-to=3 --path=/somepath mysql://root:password@server/database
```

## Installing Globally

```bash
composer global require 'byjg/migration=1.0.*'
sudo ln -s $HOME/.composer/vendor/bin/migrate /usr/local/bin
```
