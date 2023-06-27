<?php

declare(strict_types=1);

namespace App\Doctrine\Filter;

use App\Entity\Video;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Class ConvertedPublishedFilter
 * @package App\Doctrine\Filter
 */
final class ConvertedPublishedFilter extends SQLFilter
{
    /**
     * @param ClassMetadata $targetEntity
     * @param $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->getReflectionClass()->name !== Video::class) {
            return '';
        }

        return sprintf('%s.converted = TRUE AND %s.published = TRUE', $targetTableAlias, $targetTableAlias);
    }
}
