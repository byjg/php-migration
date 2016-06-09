# Database Migrations

A simple library in PHP for managing a set of database migrations using pure Sql.

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

// Create the database from "base.sql" script and run ALL existing scripts for up the database version; 
$migration->reset();

// Run ALL existing scripts for up the database version from the current version to the last version; 
$migration->up();
```

The Migration object controls the database version.  

### The Scripts

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



