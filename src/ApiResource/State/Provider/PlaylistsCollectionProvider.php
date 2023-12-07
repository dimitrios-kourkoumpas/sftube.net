<?php

declare(strict_types=1);

namespace App\ApiResource\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Playlist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PlaylistsCollectionProvider
 * @package App\ApiResource\State\Provider
 */
final readonly class PlaylistsCollectionProvider implements ProviderInterface
{
    /**
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(private EntityManagerInterface $em, private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|array|object[]|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $publicPlaylists = $this->em->getRepository(Playlist::class)->findBy(['private' => false]);

        $privatePlaylists = $user->getPlaylists(private: true)->toArray();

        $playlists = array_merge($publicPlaylists, $privatePlaylists);

        usort($playlists, function (Playlist $p1, Playlist $p2) {
            if ($p1->getName() === $p2->getName()) {
                return 0;
            }

            return $p1->getName() > $p2->getName() ? 1 : -1;
        });

        return $playlists;
    }
}
