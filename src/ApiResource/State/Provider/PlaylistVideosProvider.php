<?php

declare(strict_types=1);

namespace App\ApiResource\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\Entity\Playlist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PlaylistVideosProvider
 * @package App\ApiResource\State\Provider
 */
final readonly class PlaylistVideosProvider implements ProviderInterface
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
        $user = $this->tokenStorage->getToken()?->getUser();

        $playlist = $this->em->getRepository(Playlist::class)->find($uriVariables['id']);

        if ($playlist->isPrivate() && !$playlist->isOwner($user)) {
            throw new AccessDeniedException();
        }

        return $playlist->getVideos();
    }
}
