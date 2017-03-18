<?php

namespace ByJG\DbMigration\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create the directory structure FROM a pre-existing database')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Define the path where the base.sql resides. If not set assumes the current folder'
            )
            ->addOption(
                'migration',
                'm',
                InputOption::VALUE_NONE,
                'Create the migration script (Up and Down)'
            )
            ->addUsage('')
            ->addUsage('Example: ')
            ->addUsage('   migrate create --path /path/to/strcuture')
            ->addUsage('   migrate create --path /path/to/strcuture --migration ')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    protected function createMigrationSql($path, $startVersion)
    {
        $files = glob("$path/*.sql");
        $lastVersion = $startVersion;
        foreach ($files as $file) {
            $version = intval(basename($file));
            if ($version > $lastVersion) {
                $lastVersion = $version;
            }
        }

        $lastVersion = $lastVersion + 1;

        file_put_contents(
            "$path/" . str_pad($lastVersion, 5, '0', STR_PAD_LEFT) . ".sql",
            "-- Migrate to Version $lastVersion \n\n"
        );

        return $lastVersion;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_exists("$path/base.sql")) {
            file_put_contents("$path/base.sql", "-- Put here your base SQL");
        }

        if (!file_exists("$path/migrations")) {
            mkdir("$path/migrations", 0777, true);
            mkdir("$path/migrations/up", 0777, true);
            mkdir("$path/migrations/down", 0777, true);
        }

        if ($input->hasOption('migration')) {
            $output->writeln('Created UP version: ' . $this->createMigrationSql("$path/migrations/up", 0));
            $output->writeln('Created DOWN version: ' . $this->createMigrationSql("$path/migrations/down", -1));
        }
    }
}
