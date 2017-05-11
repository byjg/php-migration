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

class DownCommand extends ConsoleCommand
{
    protected function configure()
    {
        parent::configure(); 
        $this
            ->setName('down')
            ->setDescription('Migrate down the database version.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $versionInfo = $this->migration->getCurrentVersion();
        if (strpos($versionInfo['status'], 'partial') !== false) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'The database was not fully updated and maybe be unstable. Did you really want migrate the version? (y/N)',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Aborted.');

                return;
            }
        }

        parent::execute($input, $output);
        $this->migration->down($this->upTo, true);
    }

}
