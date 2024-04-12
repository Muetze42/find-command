<?php

namespace NormanHuth\FindCommand;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'find', description: 'Search for console commands')]
class LaravelFindCommand extends Command
{
    use FindCommandTrait;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->deepSearch = $this->option('deep');

        $this->findCommand();
    }
}
