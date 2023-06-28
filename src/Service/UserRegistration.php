<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserRegistration
 * @package App\Service
 */
final class UserRegistration
{
    /**
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(private readonly EntityManagerInterface $em, private readonly UserPasswordHasherInterface $hasher)
    {
    }

    /**
     * @param User $user
     * @return void
     */
    public function register(User $user): void
    {
        $plainPassword = $user->getPassword();

        $password = $this->hasher->hashPassword($user, $plainPassword);

        $user->setPassword($password);

        $user->setRoles([User::ROLE_USER]);

        $this->em->persist($user);
        $this->em->flush();
    }
}
