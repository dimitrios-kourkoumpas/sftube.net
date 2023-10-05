<?php

declare(strict_types=1);

namespace App\ApiResource\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ProfileProvider
 * @package App\ApiResource\State\Provider
 */
final readonly class ProfileProvider implements ProviderInterface
{
    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
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
        $filters = $this->em->getFilters();

        if ($filters->has('regularUsersFilter') && $filters->isEnabled('regularUsersFilter')) {
            $filters->disable('regularUsersFilter');
        }

        $id = $uriVariables['id'];

        $user = $this->em->getRepository(User::class)->find($id);

        if ($user->hasRole(User::ROLE_ADMIN)) {
            throw new AccessDeniedException();
        }

        $uriTemplate = $operation->getUriTemplate();

        return match ($uriTemplate) {
            '/profile/{id}' => $user,
            '/profile/{id}/videos' => $user->getVideos(),
            '/profile/{id}/playlists' => $user->getPlaylists(),
            '/profile/{id}/comments' => $user->getComments(),
            '/profile/{id}/subscribers' => $user->getSubscribers(),
            '/profile/{id}/subscriptions' => $user->getSubscriptions(),
        };
    }
}
