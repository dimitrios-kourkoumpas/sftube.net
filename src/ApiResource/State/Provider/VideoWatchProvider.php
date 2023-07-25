<?php

declare(strict_types=1);

namespace App\ApiResource\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Video;
use App\Event\VideoWatchEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class VideoWatchProvider
 * @package App\ApiResource\State\Provider
 */
final readonly class VideoWatchProvider implements ProviderInterface
{
    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(private EntityManagerInterface $em, private EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|array|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $video = $this->em->getRepository(Video::class)->find($uriVariables['id']);

        $this->dispatcher->dispatch(new VideoWatchEvent($video));

        return $video;
    }
}
