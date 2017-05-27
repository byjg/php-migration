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
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
        try {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('This will ERASE all of data in your data. Continue with this action? (y/N) ',
                false);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Aborted.');

                return;
            }

            parent::execute($input, $output);
            $this->migration->prepareEnvironment();
            $this->migration->reset($this->upTo);
        } catch (\Exception $ex) {
            $this->handleError($ex, $output);
        }
    }

}
