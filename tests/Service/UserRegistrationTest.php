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
    private MockObject $entityManager;
    private MockObject $passwordHasher;
    private UserRegistration $userRegistration;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->userRegistration = new UserRegistration($this->entityManager, $this->passwordHasher);
    }

    /**
     * @throws Exception
     */
    public function testRegister(): void
    {
        $user = $this->createMock(User::class);

        $plainPassword = 'plain_password';
        $hashedPassword = 'hashed_password';

        $user->expects($this->once())
            ->method('getPassword')
            ->willReturn($plainPassword);

        $this->passwordHasher
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

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->userRegistration->register($user);
    }
}
