<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Class ExtractVideoMessage
 * @package App\Message
 */
final class ExtractVideoMessage
{
    /**
     * @param int $videoId
     */
    public function __construct(private readonly int $videoId)
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
