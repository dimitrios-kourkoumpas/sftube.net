<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UsersFixtures
 * @package App\DataFixtures
 */
final class UsersFixtures extends Fixture
{
    private const EMAIL_DOMAIN = 'example.org';

    private const PASSWORD_PREFIX = 'P@$$W0rd';

    public const MAX_USERS = 10;

    /**
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < self::MAX_USERS; $i++) {
            $user = new User();

            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);

            $user->setEmail('user_' . ($i + 1) . '@' . self::EMAIL_DOMAIN);

            $password = $this->hasher->hashPassword($user, self::PASSWORD_PREFIX . ($i + 1));

            $user->setPassword($password);

            $user->setRoles([User::ROLE_USER]);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
