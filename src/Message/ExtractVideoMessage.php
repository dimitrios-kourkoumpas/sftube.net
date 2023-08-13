<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Class ExtractVideoMessage
 * @package App\Message
 */
final readonly class ExtractVideoMessage
{
    /**
     * @param int $videoId
     */
    public function __construct(private int $videoId)
    {
    }

    /**
     * @return int
     */
    public function getVideoId(): int
    {
        return $this->videoId;
    }
}
