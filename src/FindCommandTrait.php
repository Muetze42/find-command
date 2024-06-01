<?php

namespace NormanHuth\FindCommand;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Descriptor\ApplicationDescription;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\note;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

trait FindCommandTrait
{
    use Conditionable;

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
     * The array of available commands.
     */
    protected array $commands;

    /**
     * Execute the console command.
     */
    public function executeFindCommand(): void
    {
        $description = new ApplicationDescription($this->getApplication());
        $this->commands = Arr::where($description->getCommands(), function (Command $command) {
            return ! $command instanceof $this &&
                (! method_exists($command, 'exceptFromFindCommand') || ! $command->exceptFromFindCommand());
        });

        $this->searchCommand();
    }

    /**
     * Search for a command.
     */
    protected function searchCommand(): void
    {
        $command = search(
            label: 'Search for command',
            options: fn (string $value) => $this->search($value),
            required: true
        );

        if (windows_os() && $command == $this->getName()) {
            $this->searchCommand();

            return;
        }

        (new DescriptorHelper())->describe($this->output, $this->commands[$command]);

        $options = class_uses($this, 'Illuminate\Console\Concerns\CallsCommands') ? ['Execute the command'] : [];
        $options[] = 'Search for another command';
        $options[] = 'Exit';

        $action = select(
            label: 'Choose a action',
            options: $options,
        );

        if ($action == 'Execute the command') {
            $this->executeFoundCommand($this->commands[$command]);

            return;
        }

        if ($action == 'Search for another command') {
            $this->searchCommand();
        }
    }

    /**
     * Execute the found command.
     */
    protected function executeFoundCommand(Command $command): void
    {
        note(sprintf('Execute the „%s“ command', $command->getName()));
        $arguments = [];
        $definition = $command->getDefinition();
        $array = array_merge($definition->getArguments(), $definition->getOptions());

        foreach ($array as $key => $input) {
            if (in_array($key, ['help', 'quiet', 'ansi', 'version', 'no-interaction', 'verbose'])) {
                continue;
            }

            if ($input instanceof InputOption) {
                $key = '--' . $key;

                if (! $input->acceptValue()) {
                    $arguments[$key] = confirm(
                        label: $input->getName(),
                        default: (bool) $input->getDefault(),
                        required: false,
                        hint: $input->getDescription(),
                    );

                    continue;
                }
            }

            /* @var \Symfony\Component\Console\Input\InputArgument|\Symfony\Component\Console\Input\InputOption $input */
            if ($input->isArray()) {
                $arguments[$key] = textarea(
                    label: $input->getName(),
                    placeholder: 'one value in each line',
                    default: implode(PHP_EOL, (array) $input->getDefault()),
                    required: $input instanceof InputArgument && $input->isRequired(),
                    hint: $input->getDescription(),
                );
                $arguments[$key] = array_filter(
                    array_map('trim', explode(PHP_EOL, $arguments[$key]))
                );

                continue;
            }

            $arguments[$key] = text(
                label: $input->getName(),
                default: (string) $input->getDefault(),
                required: $input instanceof InputArgument && $input->isRequired(),
                hint: $input->getDescription(),
            );
        }

        $arguments = array_filter($arguments, fn ($argument) => $argument !== '');

        $this->call($command->getName(), $arguments);
    }

    /**
     * Search for command and return the result prioritized.
     */
    protected function search(string $value): array
    {
        if (empty(trim($value))) {
            return [];
        }

        $value = preg_split('/\s+/', $value, flags: PREG_SPLIT_NO_EMPTY);

        $result = array_merge(
            Arr::where($this->commands, fn (Command $command) => $command->getName() == $value),
            Arr::where($this->commands, fn (Command $command) => Str::containsAll($command->getName(), $value, true)),
            Arr::where($this->commands, fn (Command $command) => Str::containsAll($command->getDescription(), $value, true)),
            $this->when($this->option('deep'), fn () => $this->deepSearchItems($value), fn () => []),
        );

        $result = Arr::mapWithKeys($result, function (Command $command) {
            $label = windows_os() ? $command->getDescription() : sprintf('[%s] %s', $command->getName(), $command->getDescription());

            return [$command->getName() => $label];
        });

        if (windows_os()) {
            $result[$this->getName()] = $this->getDescription();
        }

        return $result;
    }

    /**
     * Get the deep search items.
     */
    protected function deepSearchItems(array $value): array
    {
        return Arr::where($this->commands, function (Command $command) use ($value) {
            $definition = $command->getDefinition();
            $deep = implode(PHP_EOL, array_merge(
                Arr::map($definition->getArguments(), fn (InputArgument $argument) => $argument->getDescription()),
                Arr::map($definition->getOptions(), fn (InputOption $option) => $option->getDescription()),
            ));

            return Str::containsAll($deep, $value, true);
        });
    }
}
