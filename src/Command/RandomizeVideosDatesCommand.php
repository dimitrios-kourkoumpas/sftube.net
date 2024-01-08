<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Video;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RandomizeVideosDatesCommand
 * @package App\Command
 */
#[AsCommand(
    name: 'app:randomize:videos-dates',
    description: 'Randomize videos upload and comments dates',
)]
final class RandomizeVideosDatesCommand extends Command
{
    // 4 months in seconds
    private const FOUR_MONTHS = 10519200;

    /**
     * @param EntityManagerInterface $em
     * @param string|null $name
     */
    public function __construct(private  readonly EntityManagerInterface $em, string $name = null)
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

        $io->text('Randomize videos upload dates');

        $sql = 'UPDATE `video` SET `created_at` = FROM_UNIXTIME(UNIX_TIMESTAMP(NOW()) - FLOOR(0 + (RAND() * ' . self::FOUR_MONTHS . ')))';

        $conn = $this->em->getConnection();

        try {
            $conn->executeStatement($sql);
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Done');

        $io->text('Randomize videos comments dates');

        $videos = $this->em->getRepository(Video::class)->findAll();

        foreach ($videos as $video) {
            $created_at = $video->getCreatedAt()->getTimestamp();

            $sql = 'UPDATE `comment` 
                    SET `created_at` = FROM_UNIXTIME(FLOOR(' . $created_at . ' + (RAND() * (UNIX_TIMESTAMP(NOW()) - ' . $created_at . ')))) 
                    WHERE `video_id` = ' . $video->getId();

            try {
                $conn->executeStatement($sql);
            } catch (Exception $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        $io->success('Done');

        return Command::SUCCESS;
    }
}
