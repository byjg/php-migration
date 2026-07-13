# Changelog - Version 6.0

## Overview

Version 6.0 represents a major upgrade focused on modernizing the codebase, improving developer experience, and maintaining compatibility with the latest PHP versions and dependencies.

## New Features

### Built-in CLI Tool
- **Standalone CLI without external dependencies**: Pure PHP implementation without requiring Symfony Console
- **Environment variable support**: Configure migrations using `MIGRATION_CONNECTION`, `MIGRATION_PATH`, and `MIGRATION_TABLE`
- **Comprehensive help system**: Multiple verbosity levels (`-v`, `-vv`, `-vvv`) for detailed output
- **Available commands**:
  - `version/status` - Show current database version
  - `create/install` - Create migration tracking table
  - `reset` - Reset database to base schema
  - `up` - Migrate to a specific or latest version
  - `down` - Migrate down to a specific version or version 0
  - `update` - Automatically update to latest version
- **Composer bin integration**: Automatically available as `vendor/bin/migrate`
- **Comprehensive CLI tests**: Full test coverage for CLI functionality

### Enhanced Documentation
- Complete documentation restructure with separate guides:
  - Getting Started guide
  - Database Setup guide
  - Migration Scripts guide
  - CLI Usage guide
  - API Reference guide
- Docusaurus-ready documentation format
- Improved accuracy and examples throughout

### Database Handling Improvements
- Better database name extraction for SQL Server (support for `dbname` and `Database` query parameters)
- Improved database preparation methods across all database drivers
- Enhanced SQL statement parsing for SQL Server
- Type safety improvements with PHP 8.3+ attributes

### Development Environment Enhancements
- Gitpod configuration for cloud-based development
- VS Code launch configurations
- Enhanced GitHub Actions CI workflow with SQL Server support
- Improved Docker Compose setup

## Bug Fixes

- Fixed Psalm static analysis issues
- Improved SQL statement splitting for SQL Server to handle edge cases
- Fixed database name extraction for SQL Server connections
- Enhanced type checking and error handling throughout the codebase
- Better handling of file operations with proper false checks

## Breaking Changes

| Component | Before (5.x) | After (6.x) | Description |
|-----------|-------------|------------|-------------|
| **PHP Version** | `>=8.1 <8.4` | `>=8.3 <8.6` | Minimum PHP version raised to 8.3, added support for PHP 8.4 and 8.5 |
| **PHPUnit** | `^9.6` | `^10.5\|^11.5` | Upgraded to PHPUnit 10/11 for PHP 8.3+ compatibility |
| **Psalm** | `^5.9` | `^6.13` | Upgraded to Psalm 6 for better PHP 8.3+ analysis |
| **byjg/anydataset-db** | `^5.0` | `^6.0` | Updated dependency to match version |
| **Import paths** | `use ByJG\AnyDataset\Db\DbDriverInterface` | `use ByJG\AnyDataset\Db\Interfaces\DbDriverInterface` | Interface namespace changed in anydataset-db 6.0 |
| **Type declarations** | PHP version requirement was in `require-dev` | Now in `require` | PHP version is now a production requirement |

## Upgrade Path from 5.x to 6.x

### Step 1: Check PHP Version
Ensure you're running PHP 8.3 or higher:
```bash
php -v
```

If you're on PHP 8.1 or 8.2, you need to upgrade PHP before proceeding.

### Step 2: Update composer.json
Update your `composer.json` to require the new version:
```json
{
  "require": {
    "byjg/migration": "^6.0"
  }
}
```

### Step 3: Update Dependencies
Run composer update:
```bash
composer update byjg/migration
```

This will automatically update `byjg/anydataset-db` to version 6.0 as well.

### Step 4: Update Import Statements (If Using Library Directly)
If you're using the library programmatically and importing `DbDriverInterface`, update the import:

**Before:**
```php
use ByJG\AnyDataset\Db\DbDriverInterface;
```

**After:**
```php
use ByJG\AnyDataset\Db\Interfaces\DbDriverInterface;
```

Note: This change is in the `byjg/anydataset-db` package, not in the migration package itself.

### Step 5: Update Dev Dependencies (If Applicable)
If you have PHPUnit or Psalm in your project, you may need to update them as well:
```bash
composer update --with-dependencies phpunit/phpunit vimeo/psalm
```

### Step 6: Test Your Migrations
Run your test suite to ensure everything works:
```bash
vendor/bin/phpunit
```

Test your migrations in a development environment:
```bash
# Using the new CLI tool
vendor/bin/migrate version -c <your-connection-string>
vendor/bin/migrate update -c <your-connection-string> -p ./migrations
```

### Step 7: Optional - Migrate to Built-in CLI
If you were using a custom CLI solution, you can now use the built-in CLI tool:

**Old approach (custom script):**
```php
#!/usr/bin/env php
<?php
require 'vendor/autoload.php';

$migration = new \ByJG\DbMigration\Migration($uri, $path);
$migration->update();
```

**New approach (built-in CLI):**
```bash
vendor/bin/migrate update -c <connection-uri> -p ./migrations
```

Or use environment variables:
```bash
export MIGRATION_CONNECTION="mysql://user:pass@localhost/mydb"
export MIGRATION_PATH="./migrations"
vendor/bin/migrate update
```

### Known Issues & Compatibility Notes

1. **SQL Server Connections**: If you're using SQL Server, ensure your connection string includes the database name in the query parameters (`?dbname=mydb` or `?Database=mydb`) in addition to or instead of the path component.

2. **Database Interface Changes**: If you've implemented custom database adapters, you may need to update them to match the new interface signatures and add `#[\Override]` attributes for PHP 8.3+ compatibility.

3. **Namespace Changes**: The primary breaking change comes from the `byjg/anydataset-db` v6.0 dependency, which moved interfaces to an `Interfaces` namespace. This is handled internally by the migration library, but may affect you if you're using these interfaces directly.

## Performance & Quality Improvements

- Enhanced static analysis with Psalm 6
- Improved test infrastructure with PHPUnit 10/11
- Better type safety with PHP 8.3+ features
- More robust error handling
- Enhanced CI/CD pipeline with SQL Server testing

## Migration Statistics

- 42 files changed
- 2,218 insertions(+)
- 597 deletions(-)
- Major commits: 17

## Contributors

This release includes contributions and improvements from:
- Joao Gilberto Magalhaes (@byjg)
- Community contributors

## Support

For issues or questions about this release:
- GitHub Issues: https://github.com/byjg/php-migration/issues
- Documentation: See the `docs/` directory

## License

This project continues to be licensed under the MIT License.
