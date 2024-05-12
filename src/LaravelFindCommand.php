<?php

namespace NormanHuth\FindCommand;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'find')]
class LaravelFindCommand extends Command
{
    use FindCommandTrait;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'find {--deep : Search in the arguments and options descriptions too}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search console command';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->executeFindCommand();
    }
}
