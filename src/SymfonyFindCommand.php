<?php

namespace NormanHuth\FindCommand;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'find', description: 'Search for console commands')]
class SymfonyFindCommand extends Command
{
    use FindCommandTrait;

    /**
     * Executes the command.
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->deepSearch = $input->getOption('deep');

        $this->findCommand();

        return Command::SUCCESS;
    }
}
