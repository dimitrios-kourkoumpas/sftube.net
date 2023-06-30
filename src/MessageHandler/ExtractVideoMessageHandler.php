<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Video;
use App\Message\ExtractVideoMessage;
use App\Service\VideoExtractor\VideoExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class ExtractVideoMessageHandler
 * @package App\MessageHandler
 */
#[AsMessageHandler(fromTransport: 'async')]
final class ExtractVideoMessageHandler
{
    /**
     * @param EntityManagerInterface $em
     * @param VideoExtractor $extractor
     */
    public function __construct(private readonly EntityManagerInterface $em, private readonly VideoExtractor $extractor)
    {
    }

    /**
     * @param ExtractVideoMessage $message
     * @return void
     */
    public function __invoke(ExtractVideoMessage $message): void
    {
        $filters = $this->em->getFilters();

        // otherwise find() will return NULL
        if ($filters->has('convertedPublishedFilter') && $filters->isEnabled('convertedPublishedFilter')) {
            $filters->disable('convertedPublishedFilter');
        }

        $video = $this->em->getRepository(Video::class)->find($message->getVideoId());

        $this->extractor->extract($video);

        $filters->enable('convertedPublishedFilter');

        echo $video->getTitle() . PHP_EOL;
    }
}