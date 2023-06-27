<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class InitCommand
 * @package App\Command
 */
#[AsCommand(
    name: 'app:init',
    description: 'Initializes application (database + filesystem)',
)]
final class InitCommand extends Command
{
    private const COMMANDS = [
        [
            'command' => 'app:create:filesystem',
        ], [
            'command' => 'doctrine:database:create',
            'arguments' => [
                '--if-not-exists' => true,
            ],
        ], [
            'command' => 'doctrine:schema:create',
        ], [
            'command' => 'doctrine:fixtures:load',
            'arguments' => [
                '--append' => true,
            ],
        ], [
            'command' => 'app:randomize:videos-dates',
        ], [
            'command' => 'app:randomize:comments-dates',
        ],
    ];

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (self::COMMANDS as $cmd) {
            $command = $this->getApplication()->find($cmd['command']);

            $arguments = new ArrayInput($cmd['arguments'] ?? []);

            try {
                $command->run($arguments, $output);
            } catch (ExceptionInterface $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        $io->success('Init complete!');

        return Command::SUCCESS;
    }
}
