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
 * Class TerminateCommand
 * @package App\Command
 */
#[AsCommand(
    name: 'app:terminate',
    description: 'Terminates application (database + filesystem) and message queue',
)]
final class TerminateCommand extends Command
{
    private const COMMANDS = [
        [
            'command' => 'messenger:stop-workers',
        ],
        [
            'command' => 'app:remove:filesystem',
        ], [
            'command' => 'doctrine:database:drop',
            'arguments' => [
                '--if-exists' => true,
                '--force' => true,
            ],
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

        $io->success('Termination complete!');

        return Command::SUCCESS;
    }
}
