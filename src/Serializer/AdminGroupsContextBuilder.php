<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AdminGroupsContextBuilder
 * @package App\Serializer
 */
final readonly class AdminGroupsContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @param SerializerContextBuilderInterface $decorated
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(private SerializerContextBuilderInterface $decorated, private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    /**
     * @param Request $request
     * @param bool $normalization
     * @param array|null $extractedAttributes
     * @return array
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (isset($context['groups']) && $this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            $context['groups'][] = $normalization ? 'admin:read' : 'admin:write';
        }

        return $context;
    }
}
