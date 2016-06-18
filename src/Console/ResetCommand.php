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

class ResetCommand extends ConsoleCommand
{
    protected function configure()
    {
        parent::configure(); 
        $this
            ->setName('reset')
            ->setDescription('Create a fresh new database');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->migration->prepareEnvironment();
        $this->migration->reset($this->upTo);
    }

}
