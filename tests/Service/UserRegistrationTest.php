<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserRegistration;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use PHPUnit\Framework\MockObject\MockObject;

class UserRegistrationTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testRegister(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $userRegistration = new UserRegistration($entityManager, $passwordHasher);

        $user = $this->createMock(User::class);

        $plainPassword = 'plain_password';
        $hashedPassword = 'hashed_password';

        $user->expects($this->once())
            ->method('getPassword')
            ->willReturn($plainPassword);

        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, $plainPassword)
            ->willReturn($hashedPassword);

        $user->expects($this->once())
            ->method('setPassword')
            ->with($hashedPassword);

        $user->expects($this->once())
            ->method('setRoles')
            ->with([User::ROLE_USER]);

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $userRegistration->register($user);
    }
}
