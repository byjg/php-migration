<?php

namespace Tests;

use ByJG\DbMigration\Console\MigrateCommand;
use Override;
use PHPUnit\Framework\TestCase;

class MigrateCommandTest extends TestCase
{
    protected string $testDir;
    protected string $dbPath;

    #[Override]
    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/migration-cli-test-' . uniqid();
        $this->dbPath = $this->testDir . '/test.db';

        // Create test migration directory structure
        mkdir($this->testDir);
        mkdir($this->testDir . '/migrations');
        mkdir($this->testDir . '/migrations/up');
        mkdir($this->testDir . '/migrations/down');

        // Create base.sql
        file_put_contents($this->testDir . '/base.sql', <<<'SQL'
-- @description: Base schema for testing
CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL
);
SQL
        );

        // Create migration up script
        file_put_contents($this->testDir . '/migrations/up/1.sql', <<<'SQL'
-- @description: Add email column
ALTER TABLE users ADD COLUMN email TEXT;
SQL
        );

        // Create migration down script
        file_put_contents($this->testDir . '/migrations/down/1.sql', <<<'SQL'
-- @description: Remove email column
ALTER TABLE users DROP COLUMN email;
SQL
        );
    }

    #[Override]
    protected function tearDown(): void
    {
        // Clean up test directory
        if (file_exists($this->testDir)) {
            $this->rrmdir($this->testDir);
        }
    }

    private function rrmdir($dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    private function runCommand(array $args): int
    {
        $argv = array_merge(['migrate'], $args);
        $command = new MigrateCommand($argv);

        // Capture output
        ob_start();
        $exitCode = $command->run();
        ob_end_clean();

        return $exitCode;
    }

    public function testHelpCommand(): void
    {
        ob_start();
        $exitCode = $this->runCommand(['--help']);
        $output = ob_get_clean();

        $this->assertEquals(0, $exitCode);
    }

    public function testCreateCommand(): void
    {
        $exitCode = $this->runCommand([
            'create',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->dbPath);
    }

    public function testVersionCommand(): void
    {
        // Create version table first
        $this->runCommand([
            'create',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        // Check version
        $exitCode = $this->runCommand([
            'version',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testResetCommand(): void
    {
        // Create version table first
        $this->runCommand([
            'create',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        // Reset database
        $exitCode = $this->runCommand([
            'reset',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testUpCommand(): void
    {
        // Create and reset database
        $this->runCommand([
            'create',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        $this->runCommand([
            'reset',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir,
            '--version', '0'
        ]);

        // Migrate up
        $exitCode = $this->runCommand([
            'up',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir,
            '--version', '1'
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testDownCommand(): void
    {
        // Create and reset database
        $this->runCommand([
            'create',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        $this->runCommand([
            'reset',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        // Migrate down
        $exitCode = $this->runCommand([
            'down',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir,
            '--version', '0'
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testUpdateCommand(): void
    {
        // Create and reset database
        $this->runCommand([
            'create',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir
        ]);

        $this->runCommand([
            'reset',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir,
            '--version', '0'
        ]);

        // Update to version 1
        $exitCode = $this->runCommand([
            'update',
            '-c', 'sqlite://' . $this->dbPath,
            '-p', $this->testDir,
            '--version', '1'
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testEnvironmentVariableConnection(): void
    {
        putenv('MIGRATION_CONNECTION=sqlite://' . $this->dbPath);

        $exitCode = $this->runCommand([
            'create',
            '-p', $this->testDir
        ]);

        $this->assertEquals(0, $exitCode);

        putenv('MIGRATION_CONNECTION');
    }

    public function testEnvironmentVariablePath(): void
    {
        putenv('MIGRATION_CONNECTION=sqlite://' . $this->dbPath);
        putenv('MIGRATION_PATH=' . $this->testDir);

        $exitCode = $this->runCommand(['create']);

        $this->assertEquals(0, $exitCode);

        putenv('MIGRATION_CONNECTION');
        putenv('MIGRATION_PATH');
    }

    public function testEnvironmentVariableTable(): void
    {
        putenv('MIGRATION_CONNECTION=sqlite://' . $this->dbPath);
        putenv('MIGRATION_PATH=' . $this->testDir);
        putenv('MIGRATION_TABLE=custom_migration_table');

        $exitCode = $this->runCommand(['create']);

        $this->assertEquals(0, $exitCode);

        putenv('MIGRATION_CONNECTION');
        putenv('MIGRATION_PATH');
        putenv('MIGRATION_TABLE');
    }

    public function testMissingConnectionError(): void
    {
        $exitCode = $this->runCommand([
            'version',
            '-p', $this->testDir
        ]);

        $this->assertEquals(1, $exitCode);
    }
}
