<?php
/**
 * Created by PhpStorm.
 * User: jg
 * Date: 17/06/16
 * Time: 21:52
 */

namespace ByJG\DbMigration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseVersionCommand extends ConsoleCommand
{
    protected function configure()
    {
        parent::configure(); 
        $this
            ->setName('version')
            ->setDescription('Get the current database version');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        try {
            $versionInfo = $this->migration->getCurrentVersion();
            $output->writeln('version: ' . $versionInfo['version']);
            $output->writeln('status.: ' . $versionInfo['status']);
        } catch (\Exception $ex) {
            $this->handleError($ex, $output);
        }
    }
}
