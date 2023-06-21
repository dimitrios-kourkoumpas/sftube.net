<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class RemoveFilesystemCommand
 * @package App\Command
 */
#[AsCommand(
    name: 'app:remove:filesystem',
    description: 'Removes application filesystem (directory structure)',
)]
final class RemoveFilesystemCommand extends Command
{
    /**
     * @param ParameterBagInterface $parameters
     * @param Filesystem $filesystem
     * @param string|null $name
     */
    public function __construct(private readonly ParameterBagInterface $parameters, private readonly Filesystem $filesystem, string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $directories = array_filter(
            $this->parameters->all(),
            fn(string $parameter) => str_starts_with($parameter, 'app.filesystem.') && str_ends_with($parameter, '.path'),
            ARRAY_FILTER_USE_KEY
        );

        array_walk($directories, function (string $directory) use ($io) {
            if ($this->filesystem->exists($directory)) {
                $this->filesystem->remove($directory);

                $io->text('Removed ' . $directory);
            }
        });

        $io->success('Filesystem removed!');

        return Command::SUCCESS;
    }
}
