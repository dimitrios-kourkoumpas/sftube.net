<?php

declare(strict_types=1);

namespace App\Service\VideoExtractor;

use App\Entity\Video;
use App\Service\Configurations;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class VideoExtractor
 * @package App\Service\VideoExtractor
 */
final class VideoExtractor
{
    /**
     * @var FFMpeg $ffmpeg
     */
    private FFMpeg $ffmpeg;

    /**
     * @var FFProbe $ffprobe
     */
    private FFProbe $ffprobe;

    /**
     * @var array $paths
     */
    private array $paths = [];

    /**
     * @var array $metadata
     */
    private array $metadata = [];

    /**
     * @param EntityManagerInterface $em
     * @param ParameterBagInterface $parameters
     * @param Configurations $configurations
     * @param Filesystem $filesystem
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $parameters,
        private readonly Configurations $configurations,
        private readonly Filesystem $filesystem
    )
    {
        $this->paths = array_filter(
            $this->parameters->all(),
            fn(string $path) => str_starts_with($path, 'app.filesystem.') && str_ends_with($path, '.path'),
            ARRAY_FILTER_USE_KEY
        );

        $this->ffmpeg = FFMpeg::create();
        $this->ffprobe = FFProbe::create();
    }

    /**
     * @param Video $video
     * @return void
     */
    public function extract(Video $video): void
    {
        $this->extractMetadata($video);

        $this->extractThumbnail($video);

        $extractor = VideoExtractorFactory::create($video, $this->paths);
        $extractor->extract($video);

        $this->setFlags($video);

        $this->move($video);

        $this->em->persist($video);
        $this->em->flush();
    }

    /**
     * @param Video $video
     * @return void
     */
    private function extractMetadata(Video $video): void
    {
        $this->metadata = $this
            ->ffprobe
            ->format($this->paths['app.filesystem.videos.upload.path'] . DIRECTORY_SEPARATOR . $video->getFilename())
            ->all();

        $video->setMetadata($this->metadata);
    }

    /**
     * @param Video $video
     * @return void
     */
    private function extractThumbnail(Video $video): void
    {
        $duration = $this->metadata['duration'];

        $at = (int) $duration / Video::MAX_FRAMES;

        $thumbnail = pathinfo($video->getFilename(), PATHINFO_FILENAME) . '.jpg';

        $this->ffmpeg
            ->open($this->paths['app.filesystem.videos.upload.path'] . DIRECTORY_SEPARATOR . $video->getFilename())
            ->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($at))
            ->save($this->paths['app.filesystem.images.videos.thumbnails.path'] . DIRECTORY_SEPARATOR . $thumbnail);

        $video->setThumbnail($thumbnail);
    }

    /**
     * @param Video $video
     * @return void
     */
    private function setFlags(Video $video): void
    {
        $video->setConverted(true);

        $video->setPublished(
            $this->configurations->isSet('auto-publish-videos') && $this->configurations->get('auto-publish-videos')
        );
    }

    /**
     * @param Video $video
     * @return void
     */
    private function move(Video $video): void
    {
        $this->filesystem->rename(
            $this->paths['app.filesystem.videos.upload.path'] . DIRECTORY_SEPARATOR . $video->getFilename(),
            $this->paths['app.filesystem.videos.public.path'] . DIRECTORY_SEPARATOR . $video->getFilename(),
        );
    }
}
