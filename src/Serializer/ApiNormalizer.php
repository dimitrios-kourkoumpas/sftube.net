<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use App\Entity\Video;
use App\Util\Trait\FullHost;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ApiNormalizer
 * @package App\Serializer
 */
final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    use FullHost;

    /**
     * @var DenormalizerInterface|NormalizerInterface $decorated
     */
    private DenormalizerInterface|NormalizerInterface $decorated;

    /**
     * @param NormalizerInterface $decorated
     * @param ParameterBagInterface $parameters
     * @param RouterInterface $router
     */
    public function __construct(NormalizerInterface $decorated, private readonly ParameterBagInterface $parameters, private readonly RouterInterface $router)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
    }

    /**
     * @param $data
     * @param $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @param $object
     * @param $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): mixed
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (is_array($data)) {
            $host = $this->getFullHost($this->router);

            if ($object instanceof Video) {
                if (isset($data['thumbnail'])) {
                    $thumbnail = $host . $this->parameters->get('web.images.videos.thumbnails.url_segment') . $data['thumbnail'];

                    $data['thumbnail'] = $thumbnail;
                }

                if (isset($data['filename'])) {
                    $filename = $host . $this->parameters->get('web.videos.url_segment') . $data['filename'];

                    $data['filename'] = $filename;
                }
            }

            if ($object instanceof User) {
                if (isset($data['avatar'])) {
                    $avatar = $host . $this->parameters->get('web.images.users.avatars.url_segment') . $data['avatar'];

                    $data['avatar'] = $avatar;
                }
            }
        }

        return $data;
    }

    /**
     * @param $data
     * @param $type
     * @param $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    /**
     * @param $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return mixed|string
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    /**
     * @param SerializerInterface $serializer
     * @return void
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        if($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
