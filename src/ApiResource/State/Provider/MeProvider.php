<?php

declare(strict_types=1);

namespace App\ApiResource\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Class MeProvider
 * @package App\ApiResource\State\Provider
 */
final readonly class MeProvider implements ProviderInterface
{
    /**
     * @param Security $security
     */
    public function __construct(private Security $security)
    {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|array|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $uriTemplate = $operation->getUriTemplate();

            return match ($uriTemplate) {
                '/me' => $user,
                '/me/videos' => $user->getVideos(),
                '/me/playlists' => $user->getPlaylists(private: null),
                '/me/subscriptions' => $user->getSubscriptions(),
                '/me/subscribers' => $user->getSubscribers(),
            };
        }

        return null;
    }
}
