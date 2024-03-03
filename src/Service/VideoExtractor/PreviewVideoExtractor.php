<?php

declare(strict_types=1);

namespace App\Service\VideoExtractor;

use App\Entity\Video;
use FFMpeg\FFMpeg;

/**
 * Class PreviewVideoExtractor
 * @package App\Service\VideoExtractor
 */
final class PreviewVideoExtractor implements VideoExtractorInterface
{
    private const NB_PREVIEW_CLIPS = 5;

    private const PREVIEW_CLIPS_DURATION = 2;

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
        $previewClips = [];

        $videoFile = $this->ffmpeg->open($this->paths['app.filesystem.videos.upload.path'] . DIRECTORY_SEPARATOR . $video->getFilename());

        $filename = pathinfo($video->getFilename(), PATHINFO_FILENAME);
        $extension = pathinfo($video->getFilename(), PATHINFO_EXTENSION);

        $duration = $video->getMetadata('duration');

        $format = new \FFMpeg\Format\Video\X264();

        // remove audio
        $format->setAdditionalParameters(['-an']);

        $at = 0;
        $i = 1;

        $interval = ($duration - (self::NB_PREVIEW_CLIPS * self::PREVIEW_CLIPS_DURATION)) / self::NB_PREVIEW_CLIPS;

        while ($at < $duration) {
            $clip = $videoFile->clip(
                \FFMpeg\Coordinate\TimeCode::fromSeconds($at),
                \FFMpeg\Coordinate\TimeCode::fromSeconds(self::PREVIEW_CLIPS_DURATION)
            );

            $clipFilename = $this->paths['app.filesystem.videos.previews.path'] . DIRECTORY_SEPARATOR . $filename . '-' . $i . '.' . $extension;

            $clip->save($format, $clipFilename);

            $previewClips[] = $clipFilename;

            $i++;
            $at += $interval;
        }

        $videoFile->concat($previewClips)
            ->saveFromSameCodecs($this->paths['app.filesystem.videos.previews.path'] . DIRECTORY_SEPARATOR . $video->getFilename());

        // cleanup
        foreach ($previewClips as $previewClip) {
            unlink($previewClip);
        }
    }
}
