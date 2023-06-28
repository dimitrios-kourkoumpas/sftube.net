<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Service\Configurations;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserRegistrationVoter
 * @package App\Security\Voter
 */
final class UserRegistrationVoter extends Voter
{
    public const REGISTER = 'register';

    /**
     * @param Configurations $configurations
     */
    public function __construct(private readonly Configurations $configurations)
    {
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::REGISTER;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->configurations->isSet('allow-user-signup') && $this->configurations->get('allow-user-signup');
    }
}
