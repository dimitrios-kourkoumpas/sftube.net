<?php

namespace App\Service\VideoExtractor;

use App\Entity\Video;

/**
 * Class VideoExtractorFactory
 * @package App\Service\VideoExtractor
 */
final class VideoExtractorFactory
{
    /**
     * @param Video $video
     * @param array $paths
     * @return VideoExtractorInterface
     */
    public static function create(Video $video, array $paths): VideoExtractorInterface
    {
        return match ($video->getExtractionMethod()) {
            Video::SLIDESHOW_EXTRACTION => new SlideshowVideoExtractor($paths),
            Video::PREVIEW_EXTRACTION => new PreviewVideoExtractor($paths),
            default => throw new \InvalidArgumentException('Invalid video extraction method')
        };
    }
}
