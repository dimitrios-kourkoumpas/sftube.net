<?php

declare(strict_types=1);

namespace App\ApiResource\Encoder;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * Class MultipartDecoder
 * @package App\ApiResource\Encoder
 */
final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @param string $data
     * @param string $format
     * @param array $context
     * @return array|null
     */
    public function decode(string $data, string $format, array $context = []): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        return array_map(static function ($element) {
            // handle tags array. TODO: improve this!!
            if (is_array($element)) {
                $element = explode(',', $element[0]);

                $element = json_encode($element);
            }
                // Multipart form values will be encoded in JSON.
                $decoded = json_decode($element, true);

                return is_array($decoded) ? $decoded : $element;
            }, $request->request->all()) + $request->files->all();
    }

    /**
     * @param string $format
     * @return bool
     */
    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
