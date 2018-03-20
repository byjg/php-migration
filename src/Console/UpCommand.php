<?php

namespace ByJG\DbMigration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
        try {
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
            $this->migration->up($this->upTo, true);
        } catch (\Exception $ex) {
            $this->handleError($ex, $output);
        }
    }
}
