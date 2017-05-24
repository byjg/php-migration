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

class UpdateCommand extends ConsoleCommand
{
    protected function configure()
    {
        parent::configure(); 
        $this
            ->setName('update')
            ->setDescription('Migrate Up or Down the database version based on the current database version and the ' .
                'migration scripts available'
            );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $versionInfo = $this->migration->getCurrentVersion();
        if (strpos($versionInfo['status'], 'partial') !== false) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'The database was not fully updated and maybe be unstable. Did you really want migrate the version? (y/N) ',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Aborted.');
                return;
            }
        }

        parent::execute($input, $output);
        $this->migration->update($this->upTo, true);
    }
}
