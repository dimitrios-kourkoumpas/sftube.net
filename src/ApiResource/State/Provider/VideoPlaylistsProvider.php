<?php

declare(strict_types=1);

namespace App\ApiResource\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Playlist;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class VideoPlaylistsProvider
 * @package App\ApiResource\State\Provider
 */
final readonly class VideoPlaylistsProvider implements ProviderInterface
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
        $video = $this->em->getRepository(Video::class)->find($uriVariables['id']);

        $playlists = $video->getPlaylists();

        $user = $this->tokenStorage->getToken()->getUser();

        $publicPlaylists = $playlists->filter(fn(Playlist $p) => $p->isPrivate() === false)->toArray();

        $userPlaylists = $playlists->filter(fn(Playlist $p) => $p->isPrivate() === true && $p->isOwner($user))->toArray();

        $playlists = array_merge($publicPlaylists, $userPlaylists);

        usort($playlists, function(Playlist $p1, Playlist $p2) {
            if ($p1->getName() === $p2->getName()) {
                return 0;
            }

            return $p1->getName() > $p2->getName() ? 1 : -1;
        });

        return $playlists;
    }
}
