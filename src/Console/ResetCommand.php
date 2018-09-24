<?php

namespace ByJG\DbMigration\Console;

use ByJG\DbMigration\Exception\ResetDisabledException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ResetCommand extends ConsoleCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('reset')
            ->setDescription('Create a fresh new database')
            ->addOption(
                'yes',
                null,
                InputOption::VALUE_NONE,
                'Answer yes to any interactive question'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \ByJG\DbMigration\Exception\ResetDisabledException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (getenv('MIGRATE_DISABLE_RESET') === "true") {
            throw new ResetDisabledException('Reset was disabled by MIGRATE_DISABLE_RESET environment variable. Cannot continue.');
        }

        try {
            $helper = $this->getHelper('question');
            if (!$input->getOption('yes')) {
                $question = new ConfirmationQuestion(
                    'This will ERASE all of data in your data. Continue with this action? (y/N) ',
                    false
                );

                if (!$helper->ask($input, $output, $question)) {
                    $output->writeln('Aborted.');

                    return;
                }
            }

            parent::execute($input, $output);
            $this->migration->prepareEnvironment();
            $this->migration->reset($this->upTo);
        } catch (\Exception $ex) {
            $this->handleError($ex, $output);
        }
    }
}
