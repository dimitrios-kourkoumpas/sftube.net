<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Video;
use App\Message\ExtractVideoMessage;
use App\Service\VideoExtractor\VideoExtractor;
use App\Util\Trait\FullHost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ExtractVideoMessageHandler
 * @package App\MessageHandler
 */
#[AsMessageHandler(fromTransport: 'videos')]
final readonly class ExtractVideoMessageHandler
{
    use FullHost;

    /**
     * @param EntityManagerInterface $em
     * @param VideoExtractor $extractor
     * @param HubInterface $hub
     * @param UrlGeneratorInterface $urlGenerator
     * @param ParameterBagInterface $parameters
     * @param RouterInterface $router
     */
    public function __construct(private EntityManagerInterface $em, private VideoExtractor $extractor, private HubInterface $hub, private UrlGeneratorInterface $urlGenerator, private ParameterBagInterface $parameters, private RouterInterface $router)
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

        $host = $this->getFullHost($this->router);

        // publish SSE
        $this->hub->publish(new Update('http://localhost/videos/published', json_encode([
            'title' => $video->getTitle(),
            'thumbnail' => $host . $this->parameters->get('web.images.videos.thumbnails.url_segment') . $video->getThumbnail(),
            'user' => [
                'id' => $video->getUser()->getId(),
            ],
            'url' => $this->urlGenerator->generate('app.videos.watch', [
                'id' => $video->getId(),
                'slug' => $video->getSlug(),
            ]),
            'published' => $video->isPublished(),
        ])));
    }
}