<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\VideoWatchEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Class VideWatchEventListener
 * @package App\EventListener
 */
#[AsEventListener(event: VideoWatchEvent::class)]
final readonly class VideWatchEventListener
{
    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param VideoWatchEvent $event
     * @return void
     */
    public function __invoke(VideoWatchEvent $event): void
    {
        $video = $event->getVideo();

        $video->setViews($video->getViews() + 1);

        $this->em->persist($video);
        $this->em->flush();
    }
}
