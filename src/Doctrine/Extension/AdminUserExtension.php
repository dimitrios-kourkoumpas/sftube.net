<?php

declare(strict_types=1);

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Class AdminUserExtension
 * @package App\Doctrine\Extension
 */
final readonly class AdminUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /**
     * @param Security $security
     */
    public function __construct(private Security $security)
    {
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param Operation|null $operation
     * @param array $context
     * @return void
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($this->security->isGranted(User::ROLE_ADMIN)) {
            if ($resourceClass === Video::class) {
                $em = $queryBuilder->getEntityManager();

                $filters = $em->getFilters();

                $filters->disable('convertedPublishedFilter');
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param array $identifiers
     * @param Operation|null $operation
     * @param array $context
     * @return void
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        if ($this->security->isGranted(User::ROLE_ADMIN)) {
            if ($resourceClass === Video::class) {
                $em = $queryBuilder->getEntityManager();

                $filters = $em->getFilters();

                $filters->disable('convertedPublishedFilter');
            }
        }
    }
}
