<?php

namespace App\Service\VideoExtractor;

use App\Entity\Video;

/**
 * Class SlideshowVideoExtractor
 * @package App\Service\VideoExtractor
 */
final class SlideshowVideoExtractor implements VideoExtractorInterface
{
    /**
     * @param array $paths
     */
    public function __construct(private readonly array $paths)
    {
    }

    /**
     * @param Video $video
     * @return void
     */
    public function extract(Video $video): void
    {
        dd($this->paths);
    }
}
