# Database Migrations PHP

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/migration/)
[![GitHub license](https://img.shields.io/github/license/byjg/migration.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/migration.svg)](https://github.com/byjg/migration/releases/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/migration/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/migration/?branch=master)
[![Build Status](https://github.com/byjg/migration/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/migration/actions/workflows/phpunit.yml)

## Features

This is a simple library written in PHP for database version control. Currently supports Sqlite, MySql, Sql Server and Postgres.

Database Migration can be used as:
  - Command Line Interface
  - PHP Library to be integrated in your functional tests
  - Integrated in you CI/CD indenpent of your programming language or framework.
  
Database Migrates uses only SQL commands for versioning your database.

## Why pure SQL commands?

The most of the frameworks tend to use programming statements for versioning your database instead of use pure SQL. 

There are some advantages to use the native programming language of your framework to maintain the database:
  - Framework commands have some trick codes to do complex tasks;
  - You can code once and deploy to different database systems;
  - And others

But at the end despite these good features the reality in big projects someone will use the MySQL Workbench to change your database and then spend some hours translating that code for PHP. So, why do not use the feature existing in MySQL Workbench, JetBrains DataGrip and others that provides the SQL Commands necessary to update your database and put directly into the database versioning system?

Because of that this is an agnostic project (independent of framework and Programming Language) and use pure and native SQL commands for migrate your database.

## Installing

### PHP Library

If you want to use only the PHP Library in your project:

```
composer require "byjg/migration":"4.2.*"
```
### Command Line Interface

The command line interface is standalone and does not require you install with your project.

You can install global and create a symbolic lynk 

```
composer require "byjg/migration-cli":"4.1.*"
```

Please visit https://github.com/byjg/migration-cli to get more informations about Migration CLI.

## Supported databases:

| Database      | Driver                                                                          | Connection String                                        |
| --------------| ------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  |  sqlite:///path/to/file                                  |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database         |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database          |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database         |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database        |


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


## Using the PHP API and Integrate it into your projects.

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

// Add a callback progress function to receive info from the execution
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Restore the database using the "base.sql" script
// and run ALL existing scripts for up the database version to the latest version
$migration->reset();

// Run ALL existing scripts for up or down the database version
// from the current version until the $version number;
// If the version number is not specified migrate until the last database version 
$migration->update($version = null);
```

The Migration object controls the database version.


### Creating a version control in your project:

```php
<?php
// Create the Migration instance
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Register the Database or Databases can handle that URI:
$migration->registerDatabase('mysql', \ByJG\DbMigration\Database\MySqlDatabase::class);

// This command will create the version table in your database
$migration->createVersion();
```

### Getting the current version

```php
<?php
$migration->getCurrentVersion();
```

### Add Callback to control the progress

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Doing Command: $command at version $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Getting the Db Driver instance

```php
<?php
$migration->getDbDriver();
```

To use it, please visit: https://github.com/byjg/anydataset-db


## Tips on writing SQL migrations for Postgres

### Rely on explicit transactions

```sql
-- DO
BEGIN;

ALTER TABLE 1;
UPDATE 1;
UPDATE 2;
UPDATE 3;
ALTER TABLE 2;

COMMIT;


-- DON'T
ALTER TABLE 1;
UPDATE 1;
UPDATE 2;
UPDATE 3;
ALTER TABLE 2;
```

It is generally desirable to wrap migration scripts inside a `BEGIN; ... COMMIT;` block.
This way, if _any_ of the inner statements fail, _none_ of them are committed and the
database does not end up in an inconsistent state.

Mind that in case of a failure `byjg/migration` will always mark the migration as `partial`
and warn you when you attempt to run it again. The difference is that with explicit
transactions you know that the database cannot be in an inconsistent state after an
unexpected failure.

### On creating triggers and SQL functions

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Check that empname and salary are given
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null'; -- it doesn't matter if these comments are blank or not
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname; --
        END IF; --

        -- Who works for us when they must pay for it?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname; --
        END IF; --

        -- Remember who changed the payroll when
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Check that empname and salary are given
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname;
        END IF;

        -- Who works for us when they must pay for it?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname;
        END IF;

        -- Remember who changed the payroll when
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Since the `PDO` database abstraction layer cannot run batches of SQL statements,
when `byjg/migration` reads a migration file it has to split up the whole contents of the SQL
file at the semicolons, and run the statements one by one. However, there is one kind of
statement that can have multiple semicolons in-between its body: functions.

In order to be able to parse functions correctly, `byjg/migration` 2.1.0 started splitting migration
files at the `semicolon + EOL` sequence instead of just the semicolon. This way, if you append an empty
comment after every inner semicolon of a function definition `byjg/migration` will be able to parse it.

Unfortunately, if you forget to add any of these comments the library will split the `CREATE FUNCTION` statement in
multiple parts and the migration will fail.

### Avoid the colon character (`:`)

```sql
-- DO
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- DON'T
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Since `PDO` uses the colon character to prefix named parameters in prepared statements, its use will trip it
up in other contexts.

For instance, PostgreSQL statements can use `::` to cast values between types. On the other hand `PDO` will
read this as an invalid named parameter in an invalid context and fail when it tries to run it.

The only way to fix this inconsistency is avoiding colons altogether (in this case, PostgreSQL also has an alternative
 syntax: `CAST(value AS type)`).

### Use an SQL editor

Finally, writing manual SQL migrations can be tiresome, but it is significantly easier if
you use an editor capable of understanding the SQL syntax, providing autocomplete,
introspecting your current database schema and/or autoformatting your code.


## Handling different migration inside one schema

If you need to create different migration scripts and version inside the same schema it is possible
but is too risky and I **do not** recommend at all. 

To do this, you need to create different "migration tables" by passing the parameter to the constructor. 

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

For security reasons, this feature is not available at command line, but you can use the environment variable
`MIGRATION_VERSION` to store the name. 

We really recommend do not use this feature. The recommendation is one migration for one schema. 


## Running Unit tests

Basic unit tests can be running by:

```bash
vendor/bin/phpunit
```

## Running database tests

Run integration tests require you to have the databases up and running. We provided a basic `docker-compose.yml` and you
can use to start the databases for test.

**Running the databases**

```bash
docker-compose up -d postgres mysql mssql
```

**Run the tests**

```
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase* 
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase* 
```

Optionally you can set the host and password used by the unit tests

```bash
export MYSQL_TEST_HOST=localhost     # defaults to localhost
export MYSQL_PASSWORD=newpassword    # use '.' if want have a null password
export PSQL_TEST_HOST=localhost      # defaults to localhost
export PSQL_PASSWORD=newpassword     # use '.' if want have a null password
export MSSQL_TEST_HOST=localhost     # defaults to localhost
export MSSQL_PASSWORD=Pa55word            
export SQLITE_TEST_HOST=/tmp/test.db      # defaults to /tmp/test.db
```


## Related Projects

- [Micro ORM](https://github.com/byjg/micro-orm)
- [Anydataset](https://github.com/byjg/anydataset)
- [PHP Rest Template](https://github.com/byjg/php-rest-template)
- [USDocker](https://github.com/usdocker/usdocker)

----
[Open source ByJG](http://opensource.byjg.com)
