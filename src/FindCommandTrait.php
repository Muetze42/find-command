<?php

namespace NormanHuth\FindCommand;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
    /**
     * The array of available commands.
     */
    protected array $commands;

    /**
     * Names of commands wich should except from search.
     */
    protected array $exceptFromSearch = [
        //'list',
    ];

    /**
     * Determine the number of options before the list begins to scroll.
     */
    protected int $searchScroll = 10;

    /**
     * Determine if command should Search in the arguments and options descriptions too.
     */
    protected bool $deepSearch;

    protected function configure(): void
    {
        $this->addOption(
            'deep',
            'd',
            InputOption::VALUE_NONE,
            'Search in the arguments and options descriptions too'
        );
    }

    /**
     * Execute the command.
     */
    public function findCommand(): void
    {
        $description = new ApplicationDescription($this->getApplication());
        $this->exceptFromSearch[] = $this->getName();

        $this->commands = collect($description->getCommands())
            ->filter(fn (Command $command, string $key) => !in_array($key, $this->exceptFromSearch))
            ->map(function (Command $command) {
                $definition = $command->getDefinition();
                $arguments = $definition->getArguments();
                $options = $definition->getOptions();
                $deep = '';

                if ($this->deepSearch) {
                    $deep = implode("\n", (array_merge(
                        Arr::map($arguments, fn (InputArgument $argument) => $argument->getDescription()),
                        Arr::map($options, fn (InputOption $option) => $option->getDescription()),
                    )));
                }

                return [
                    'object' => $command,
                    'name' => $command->getName(),
                    'arguments' => $arguments,
                    'options' => $options,
                    'description' => $command->getDescription(),
                    'name_description' => windows_os() ? $command->getDescription() :
                        sprintf('[%s] %s', $command->getName(), $command->getDescription()),
                    'deep' => $deep,
                ];
            })
            ->toArray();

        $this->determineCommand();
    }

    protected function getActions(): array
    {
        $actions = [
            1 => 'Execute the command',
            2 => 'Display help for the given command',
            3 => 'Display help for the given command and search for another command',
        ];

        if (!$this instanceof \Illuminate\Console\Command) {
            unset($actions[1]);
        }

        return $actions;
    }

    protected function determineCommand(): void
    {
        $command = search(
            label: 'Search for command',
            options: fn (string $value) => $this->search($value),
            scroll: $this->searchScroll,
            required: true
        );

        if ($command == $this->getName()) {
            $this->determineCommand();

            return;
        }

        $action = select(
            label: 'Choose a action',
            options: $this->getActions(),
        );

        if ($action == 1) {
            $this->handleCommand($this->commands[$command]);

            return;
        }

        (new DescriptorHelper())->describe($this->output, $this->commands[$command]['object']);

        if ($action == 3) {
            $this->determineCommand();
        }
    }

    protected function handleCommand(array $command): void
    {
        note(sprintf('Execute the „%s“ command', $command['name']));
        $arguments = [];

        foreach (['arguments', 'options'] as $definition) {
            foreach ($command[$definition] as $key => $input) {
                if ($input instanceof InputOption) {
                    $key = '--' . $key;

                    if (!$input->acceptValue()) {
                        $arguments[$key] = confirm(
                            label: $input->getName(),
                            default: $input->getDefault(),
                            required: false,
                            hint: $input->getDescription(),
                        );

                        continue;
                    }
                }

                /* @var InputArgument|InputOption $input */
                if (!$input->isArray()) {
                    $arguments[$key] = textarea(
                        label: $input->getName(),
                        placeholder: 'one value in each line',
                        default: $input->getDefault(),
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
                    default: $input->getDefault(),
                    required: $input instanceof InputArgument && $input->isRequired(),
                    hint: $input->getDescription(),
                );
            }
        }

        $this->call($command['name'], $arguments);
    }

    protected function search(string $value): array
    {
        if (empty($value)) {
            return [];
        }

        /* Result with priority. */
        $result = array_merge(
            Arr::where($this->commands, fn (array $command) => $command['name'] == $value),
            Arr::where($this->commands, fn (array $command) => $this->strContainsAll($command['name'], $value)),
            Arr::where($this->commands, fn (array $command) => $this->strContainsAll($command['description'], $value)),
        );
        if ($this->deepSearch) {
            $result = array_merge(
                $result,
                Arr::where($this->commands, fn (array $command) => $this->strContainsAll($command['deep'], $value))
            );
        }

        if (windows_os()) {
            $result[] = [
                'name' => $this->getName(),
                'name_description' => $this->getDescription(),
            ];
        }

        return Arr::pluck($result, 'name_description', 'name');
    }

    protected function strContainsAll(string $haystack, string $search): bool
    {
        $haystack = Str::lower($haystack);
        $search = Str::lower($search);

        foreach (array_filter(explode(' ', $search)) as $needle) {
            if (!str_contains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }
}
