<?php

declare(strict_types=1);

namespace App\Service\VideoExtractor;

use App\Entity\Frame;
use App\Entity\Video;
use FFMpeg\FFMpeg;

/**
 * Class SlideshowVideoExtractor
 * @package App\Service\VideoExtractor
 */
final class SlideshowVideoExtractor implements VideoExtractorInterface
{
    /**
     * @var FFMpeg $ffmpeg
     */
    private FFMpeg $ffmpeg;

    /**
     * @param array $paths
     */
    public function __construct(private readonly array $paths)
    {
        $this->ffmpeg = FFMpeg::create();
    }

    /**
     * @param Video $video
     * @return void
     */
    public function extract(Video $video): void
    {
        $duration = $video->getMetadata('duration');

        $interval = (int) $duration / Video::MAX_FRAMES;

        $videoFile = $this->ffmpeg->open($this->paths['app.filesystem.videos.upload.path'] . DIRECTORY_SEPARATOR . $video->getFilename());

        $at = $interval;
        $i = 1;

        $directory = $filename = pathinfo($video->getFilename(), PATHINFO_FILENAME);

        if (!file_exists($this->paths['app.filesystem.images.videos.frames.path'] . DIRECTORY_SEPARATOR . $directory)) {
            mkdir($this->paths['app.filesystem.images.videos.frames.path'] . DIRECTORY_SEPARATOR . $directory,0777, true);
        }

        while ($at < $duration) {
            $frameFilename = $filename . '-' . $i . '.jpg';

            $videoFile
                ->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($at))
                ->save($this->paths['app.filesystem.images.videos.frames.path'] . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $frameFilename);

            $frame = new Frame();

            $frame->setUrl($directory . '/' . $frameFilename);

            $video->addFrame($frame);

            $at += $interval;
            $i++;
        }
    }
}
