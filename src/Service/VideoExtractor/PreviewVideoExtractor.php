<?php

namespace App\Service\VideoExtractor;

use App\Entity\Video;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;

/**
 * Class PreviewVideoExtractor
 * @package App\Service\VideoExtractor
 */
final class PreviewVideoExtractor implements VideoExtractorInterface
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
     * @var array $metadata
     */
    private array $metadata = [];

    /**
     * @param array $paths
     */
    public function __construct(private readonly array $paths)
    {

        $this->ffmpeg = FFMpeg::create();
        $this->ffprobe = FFProbe::create();
    }

    /**
     * @param Video $video
     * @return void
     */
    public function extract(Video $video): void
    {
        dd($this->paths);
    }


    /**
     * @param Video $video
     * @return void
     */
    private function extractMetadata(Video $video): void
    {
        $this->metadata = $this->ffprobe->format($this->paths['app.filesystem.videos.upload.path'] . DIRECTORY_SEPARATOR . $video->getFilename())->all();
    }
}
