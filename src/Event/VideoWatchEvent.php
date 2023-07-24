<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Video;

/**
 * Class VideoWatchEvent
 * @package App\Event
 */
final readonly class VideoWatchEvent
{
    /**
     * @param Video $video
     */
    public function __construct(private Video $video)
    {
    }

    /**
     * @return Video
     */
    public function getVideo(): Video
    {
        return $this->video;
    }
}
