<?php

namespace ByJG\DbMigration\Console;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ConsoleCommand extends Command
{
    protected function configure()
    {
        $this
            ->addArgument(
                'connection',
                InputArgument::OPTIONAL,
                'The connection string. Ex. mysql://root:password@server/database',
                getenv('MIGRATE_CONNECTION')
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Define the path where the base.sql resides. If not set assumes the current folder'
            )
            ->addOption(
                'up-to',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Run up to the specified version'
            )
            ->addUsage('')
            ->addUsage('Example: ')
            ->addUsage('   migrate reset mysql://root:password@server/database')
            ->addUsage('   migrate up mysql://root:password@server/database')
            ->addUsage('   migrate down mysql://root:password@server/database')
            ->addUsage('   migrate up --up-to=10 --path=/somepath mysql://root:password@server/database')
            ->addUsage('   migrate down --up-to=3 --path=/somepath mysql://root:password@server/database')
        ;
    }

    /**
     * @var Migration
     */
    protected $migration;

    protected $upTo;

    protected $connection;

    protected $path;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->connection = $input->getArgument('connection');
        if (!$this->connection) {
            throw new InvalidArgumentException('You need to setup the connection in the argument or setting the environment MIGRATE_CONNECTION');
        }

        $this->path = $input->getOption('path');
        if (!$this->path) {
            $this->path = ".";
        }
        $this->path = realpath($this->path);

        $this->upTo = $input->getOption('up-to');

        $uri = new Uri($this->connection);
        $this->migration = new Migration($uri, $this->path);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Connection String: ' . $this->connection);
            $output->writeln('Path: ' . $this->path);
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->migration->addCallbackProgress(function($command, $version) use ($output) {
                $output->writeln('Doing: ' . $command . " to " . $version);
            });
        }
    }

    protected function handleError($ex, OutputInterface $output)
    {
        $output->writeln('-- Error migrating tables --');
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(get_class($ex));
            $output->writeln($ex->getMessage());
        }
    }


}
