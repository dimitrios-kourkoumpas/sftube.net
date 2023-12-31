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
 * Class RandomizeCommentsDatesCommand
 * @package App\Command
 */
#[AsCommand(
    name: 'app:randomize:comments-dates',
    description: 'Randomize videos comments dates',
)]
final class RandomizeCommentsDatesCommand extends Command
{
    /**
     * @param EntityManagerInterface $em
     * @param string|null $name
     */
    public function __construct(private readonly EntityManagerInterface $em, string $name = null)
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

        $io->text('Randomize videos comments dates');

        $videos = $this->em->getRepository(Video::class)->findAll();

        $conn = $this->em->getConnection();

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
