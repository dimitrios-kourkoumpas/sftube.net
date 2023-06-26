<?php

namespace App\Service\VideoExtractor;

use App\Entity\Video;

/**
 * Interface VideoExtractorInterface
 * @package App\Service\VideoExtractor
 */
interface VideoExtractorInterface
{
    /**
     * @param Video $video
     * @return void
     */
    public function extract(Video $video): void;
}
