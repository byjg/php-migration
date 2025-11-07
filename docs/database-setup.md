---
sidebar_position: 2
title: Database Setup
description: Configure database connections for MySQL, PostgreSQL, SQLite, and SQL Server
---

# Database Setup and Configuration

## Supported Databases

The library supports the following databases:

| Database      | Driver               | Connection String                                 |
|---------------|----------------------|---------------------------------------------------|
| MySQL/MariaDB | pdo_mysql            | mysql://username:password@hostname:port/database  |
| PostgreSQL    | pdo_pgsql            | pgsql://username:password@hostname:port/database  |
| SQLite        | pdo_sqlite           | sqlite:///path/to/file                            |
| SQL Server    | pdo_dblib (Linux)    | dblib://username:password@hostname:port/database  |
| SQL Server    | pdo_sqlsrv (Windows) | sqlsrv://username:password@hostname:port/database |

## Database-Specific Setup

### MySQL/MariaDB

1. Required PDO Extension:
   ```bash
   sudo apt-get install php-mysql    # Debian/Ubuntu
   sudo yum install php-mysql        # RHEL/CentOS
   ```

2. Connection String Format:
   ```
   mysql://username:password@hostname:port/database
   ```

3. Optional Parameters:
   ```
   mysql://username:password@hostname:port/database?param1=value1&param2=value2
   ```

4. Transaction Limitations:

:::warning MySQL Transaction Limitations
MySQL does not support DDL (Data Definition Language) statements within transactions. Any transaction settings will be ignored for DDL operations like CREATE TABLE, ALTER TABLE, etc.
:::

### PostgreSQL

1. Required PDO Extension:
   ```bash
   sudo apt-get install php-pgsql    # Debian/Ubuntu
   sudo yum install php-pgsql        # RHEL/CentOS
   ```

2. Connection String Format:
   ```
   pgsql://username:password@hostname:port/database
   ```

3. Transaction Support:
   - Full DDL transaction support
   - Recommended to use transactions for migrations

### SQLite

1. Required PDO Extension:
   ```bash
   sudo apt-get install php-sqlite3   # Debian/Ubuntu
   sudo yum install php-sqlite3       # RHEL/CentOS
   ```

2. Connection String Format:
   ```
   sqlite:///path/to/database.sqlite
   ```

3. Transaction Support:
   - Full DDL transaction support
   - Transactions enabled by default

### SQL Server

#### Linux (FreeTDS)

1. Required Packages:
   ```bash
   sudo apt-get install php-sybase    # Debian/Ubuntu
   sudo yum install php-mssql         # RHEL/CentOS
   ```

2. Connection String:
   ```
   dblib://username:password@hostname:port/database
   ```

#### Windows (Native Driver)

1. Required:
   - Microsoft ODBC Driver for SQL Server
   - PHP SQL Server extension

2. Connection String:
   ```
   sqlsrv://username:password@hostname:port/database
   ```

## Connection Examples

### Basic Connections

```php
// MySQL
$uri = new \ByJG\Util\Uri('mysql://user:pass@localhost/database');

// PostgreSQL
$uri = new \ByJG\Util\Uri('pgsql://user:pass@localhost/database');

// SQLite
$uri = new \ByJG\Util\Uri('sqlite:///path/to/database.sqlite');

// SQL Server (Linux)
$uri = new \ByJG\Util\Uri('dblib://user:pass@localhost/database');

// SQL Server (Windows)
$uri = new \ByJG\Util\Uri('sqlsrv://user:pass@localhost/database');
```

### With Optional Parameters

```php
// MySQL with charset and SSL
$uri = new \ByJG\Util\Uri(
    'mysql://user:pass@localhost/database?charset=utf8mb4&ssl=true'
);

// PostgreSQL with schema and SSL mode
$uri = new \ByJG\Util\Uri(
    'pgsql://user:pass@localhost/database?search_path=public&sslmode=require'
);
```

## Environment Variables

For testing, you can configure database connections using environment variables:

```bash
# MySQL
export MYSQL_TEST_HOST=localhost
export MYSQL_PASSWORD=password

# PostgreSQL
export PSQL_TEST_HOST=localhost
export PSQL_PASSWORD=password

# SQL Server
export MSSQL_TEST_HOST=localhost
export MSSQL_PASSWORD=password

# SQLite
export SQLITE_TEST_HOST=/tmp/test.db
``` 