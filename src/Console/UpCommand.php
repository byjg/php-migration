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

class UpCommand extends ConsoleCommand
{
    protected function configure()
    {
        parent::configure(); 
        $this
            ->setName('up')
            ->setDescription('Migrate Up the database version');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->migration->up($this->upTo);
    }
}
