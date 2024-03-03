<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Class UploadedFileDenormalizer
 * @package App\Serializer
 */
final class UploadedFileDenormalizer implements DenormalizerInterface
{
    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return UploadedFile
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): UploadedFile
    {
        return $data;
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $data instanceof UploadedFile;
    }

    /**
     * @param string|null $format
     * @return string[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return ['*'];
    }
}
