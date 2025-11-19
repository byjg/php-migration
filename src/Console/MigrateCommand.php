<?php

namespace ByJG\DbMigration\Console;

use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Database\PgsqlDatabase;
use ByJG\DbMigration\Database\SqliteDatabase;
use ByJG\DbMigration\Database\DblibDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

class MigrateCommand
{
    private array $argv;
    private array $options = [];
    private string $command = '';
    private int $verbosity = 0;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->parseArguments();
    }

    private function parseArguments(): void
    {
        $args = array_slice($this->argv, 1);

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            if ($arg === '--help' || $arg === '-h') {
                $this->options['help'] = true;
                continue;
            }

            if (str_starts_with($arg, '--')) {
                $key = substr($arg, 2);
                $value = true;

                if (isset($args[$i + 1]) && !str_starts_with($args[$i + 1], '-')) {
                    $value = $args[$i + 1];
                    $i++;
                }

                $this->options[$key] = $value;
            } elseif (str_starts_with($arg, '-')) {
                $key = substr($arg, 1);

                if ($key === 'v') {
                    $this->verbosity = 1;
                } elseif ($key === 'vv') {
                    $this->verbosity = 2;
                } elseif ($key === 'vvv') {
                    $this->verbosity = 3;
                } else {
                    $value = true;
                    if (isset($args[$i + 1]) && !str_starts_with($args[$i + 1], '-')) {
                        $value = $args[$i + 1];
                        $i++;
                    }
                    $this->options[$key] = $value;
                }
            } else {
                if (empty($this->command)) {
                    $this->command = $arg;
                }
            }
        }
    }

    public function run(): int
    {
        if (isset($this->options['help']) || empty($this->command)) {
            $this->showHelp();
            return 0;
        }

        try {
            return match ($this->command) {
                'version', 'status' => $this->handleVersion(),
                'reset' => $this->handleReset(),
                'up' => $this->handleUp(),
                'down' => $this->handleDown(),
                'update' => $this->handleUpdate(),
                'create', 'install' => $this->handleCreate(),
                default => $this->showError("Unknown command: {$this->command}")
            };
        } catch (\Exception $e) {
            $this->output("Error: " . $e->getMessage(), 0, STDERR);
            if ($this->verbosity >= 3) {
                $this->output($e->getTraceAsString(), 0, STDERR);
            }
            return 1;
        }
    }

    private function getMigration(): Migration
    {
        $connection = $this->getOption('connection', 'c') ?: getenv('MIGRATION_CONNECTION');
        $path = $this->getOption('path', 'p') ?: getenv('MIGRATION_PATH') ?: '.';
        $migrationTable = $this->getOption('table', 't') ?: getenv('MIGRATION_TABLE') ?: 'migration_version';

        if (!$connection) {
            throw new \InvalidArgumentException(
                "Database connection URI is required. Use --connection or set MIGRATION_CONNECTION environment variable."
            );
        }

        $this->output("Connection: $connection", 2);
        $this->output("Path: $path", 2);
        $this->output("Table: $migrationTable", 2);

        $uri = new Uri($connection);

        // Register database drivers
        Migration::registerDatabase(MySqlDatabase::class);
        Migration::registerDatabase(PgsqlDatabase::class);
        Migration::registerDatabase(SqliteDatabase::class);
        Migration::registerDatabase(DblibDatabase::class);

        $noBase = isset($this->options['no-base']);

        $migration = new Migration($uri, $path, !$noBase, $migrationTable);

        if (!isset($this->options['no-transaction'])) {
            $migration->withTransactionEnabled(true);
        }

        if ($this->verbosity > 0) {
            $migration->addCallbackProgress(function ($action, $version, $fileInfo) {
                $status = $fileInfo['exists'] ? 'OK' : 'Not found';
                $this->output("  [$action] Version $version - {$fileInfo['description']} [$status]", 1);
            });
        }

        return $migration;
    }

    private function handleVersion(): int
    {
        $migration = $this->getMigration();

        try {
            $versionInfo = $migration->getCurrentVersion();
            $this->output("Current version: {$versionInfo['version']}");
            $this->output("Status: {$versionInfo['status']}");
            return 0;
        } catch (\Exception $e) {
            $this->output("Database is not versioned. Run 'migrate create' first.", 0, STDERR);
            return 1;
        }
    }

    private function handleReset(): int
    {
        $migration = $this->getMigration();
        $version = $this->getOptionAsInt('version', 'u');

        $this->output("Resetting database" . ($version ? " to version $version" : "") . "...");
        $migration->reset($version);
        $this->output("Database reset successfully!");

        return 0;
    }

    private function handleUp(): int
    {
        $migration = $this->getMigration();
        $version = $this->getOptionAsInt('version', 'u');
        $force = isset($this->options['force']);

        $this->output("Migrating up" . ($version ? " to version $version" : " to latest version") . "...");
        $migration->up($version, $force);

        $versionInfo = $migration->getCurrentVersion();
        $this->output("Migration completed! Current version: {$versionInfo['version']}");

        return 0;
    }

    private function handleDown(): int
    {
        $migration = $this->getMigration();
        $version = $this->getOptionAsInt('version', 'u');
        $force = isset($this->options['force']);

        $this->output("Migrating down" . ($version !== null ? " to version $version" : " to version 0") . "...");
        $migration->down($version, $force);

        $versionInfo = $migration->getCurrentVersion();
        $this->output("Migration completed! Current version: {$versionInfo['version']}");

        return 0;
    }

    private function handleUpdate(): int
    {
        $migration = $this->getMigration();
        $version = $this->getOptionAsInt('version', 'u');
        $force = isset($this->options['force']);

        $this->output("Updating database" . ($version ? " to version $version" : "") . "...");
        $migration->update($version, $force);

        $versionInfo = $migration->getCurrentVersion();
        $this->output("Update completed! Current version: {$versionInfo['version']}");

        return 0;
    }

    private function handleCreate(): int
    {
        $migration = $this->getMigration();

        $this->output("Creating migration version table...");
        $migration->createVersion();
        $this->output("Migration version table created successfully!");

        return 0;
    }

    private function getOption(string $long, ?string $short = null, mixed $default = null): mixed
    {
        if (isset($this->options[$long])) {
            return $this->options[$long];
        }

        if ($short && isset($this->options[$short])) {
            return $this->options[$short];
        }

        return $default;
    }

    private function getOptionAsInt(string $long, ?string $short = null): ?int
    {
        $value = $this->getOption($long, $short);
        return $value !== null ? (int)$value : null;
    }

    private function output(string $message, int $requiredVerbosity = 0, $stream = STDOUT): void
    {
        if ($this->verbosity >= $requiredVerbosity) {
            fwrite($stream, $message . "\n");
        }
    }

    private function showError(string $message): int
    {
        $this->output($message, 0, STDERR);
        $this->output("Run 'migrate --help' for usage information.", 0, STDERR);
        return 1;
    }

    private function showHelp(): void
    {
        $help = <<<'HELP'
Database Migration Tool

USAGE:
  migrate <command> [options]

COMMANDS:
  version            Show current database version (alias: status)
  create             Create migration version table (alias: install)
  reset              Reset database to base.sql and optionally migrate to a version
  up                 Migrate up to a specific version or latest
  down               Migrate down to a specific version or 0
  update             Intelligently migrate up or down to a specific version

OPTIONS:
  -c, --connection <uri>    Database connection URI (required)
                            Examples:
                              mysql://user:pass@localhost/dbname
                              pgsql://user:pass@localhost/dbname
                              sqlite:///path/to/database.db

  -p, --path <path>         Path to migration folder (default: .)
  -u, --version <version>   Target version for migration
  -t, --table <name>        Migration version table name (default: migration_version)

  --force                   Force migration even if database is in partial state
  --no-transaction          Disable transaction support
  --no-base                 Skip base.sql validation

  -v, -vv, -vvv            Increase verbosity
  -h, --help               Show this help message

ENVIRONMENT VARIABLES:
  MIGRATION_CONNECTION      Database connection URI (fallback if --connection not provided)
  MIGRATION_PATH            Path to migration folder (fallback if --path not provided)
  MIGRATION_TABLE           Migration version table name (fallback if --table not provided)
  DEBUG=true                Show stack trace on errors

EXAMPLES:
  # Show current version
  migrate version --connection mysql://user:pass@localhost/db

  # Create version table
  migrate create -c mysql://user:pass@localhost/db

  # Reset database and migrate to version 5
  migrate reset -c mysql://user:pass@localhost/db --version 5

  # Migrate up to latest version
  migrate up -c mysql://user:pass@localhost/db -vv

  # Migrate down to version 3
  migrate down -c mysql://user:pass@localhost/db --version 3

  # Update to specific version (up or down automatically)
  migrate update -c mysql://user:pass@localhost/db --version 10

  # Using environment variables
  export MIGRATION_CONNECTION="mysql://user:pass@localhost/db"
  export MIGRATION_PATH="./migrations"
  migrate version
  migrate up

HELP;

        echo $help . "\n";
    }
}