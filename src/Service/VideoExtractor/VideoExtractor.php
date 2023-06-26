<?php

namespace App\Service\VideoExtractor;

use App\Entity\Video;

/**
 * Class VideoExtractor
 * @package App\Service\VideoExtractor
 */
final class VideoExtractor
{
    /**
     * @var VideoExtractorInterface $extractor
     */
    private VideoExtractorInterface $extractor;

    /**
     * @param Video $video
     * @param array $paths
     */
    public function __construct(private readonly Video $video, private readonly array $paths)
    {
        $this->extractor = VideoExtractorFactory::create($video, $this->paths);
    }

    /**
     * @param Video $video
     * @return void
     */
    public function extract(Video $video): void
    {
        $this->extractor->extract($video);
    }
}
