<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ApiResource\State\Provider\AdminDashboardProvider;

/**
 * Class Dashboard
 * @package App\Entity
 */
#[ApiResource(
    operations: [
        new Get(
            routePrefix: 'admin',
            uriTemplate: '/dashboard',
            security: 'is_granted(\'' . User::ROLE_ADMIN . '\')',
            provider: AdminDashboardProvider::class,
            normalizationContext: [
                'groups' => [
                    'videos:collection:get',
                    'users:collection:get',
                ],
            ]
        ),
    ]
)]
final class Dashboard
{
}
