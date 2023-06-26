<?php

namespace App\Doctrine\Filter;

use App\Entity\User;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Class RegularUsersFilter
 * @package App\Doctrine\Filter
 */
final class RegularUsersFilter extends SQLFilter
{
    /**
     * @param ClassMetadata $targetEntity
     * @param $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->getReflectionClass()->name !== User::class) {
            return '';
        }

        return $targetTableAlias . '.roles LIKE \'%' . User::ROLE_USER . '%\'';
    }
}
