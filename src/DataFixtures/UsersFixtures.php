<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Util\FileRenamer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Inflector\Rules\English\Inflectible;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
     * @param ParameterBagInterface $parameters
     */
    public function __construct(private readonly UserPasswordHasherInterface $hasher, private readonly ParameterBagInterface $parameters)
    {
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $avatarsDataPath = $this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR;

        // copy generic avatar
        if (!file_exists($this->parameters->get('app.filesystem.images.users.avatars.path') . DIRECTORY_SEPARATOR . User::GENERIC_AVATAR)) {
            copy(
                $avatarsDataPath . User::GENERIC_AVATAR,
                $this->parameters->get('app.filesystem.images.users.avatars.path') . DIRECTORY_SEPARATOR . User::GENERIC_AVATAR
            );
        }

        $faker = \Faker\Factory::create();

        foreach (glob($avatarsDataPath . '*.jpg') as $i => $avatar) {
            $user = new User();

            $user->setFirstname($faker->firstName($this->getGenderFromAvatar($avatar)));
            $user->setLastname($faker->lastName);

            $user->setEmail('user_' . ($i + 1) . '@' . self::EMAIL_DOMAIN);

            $password = $this->hasher->hashPassword($user, self::PASSWORD_PREFIX . ($i + 1));

            $user->setPassword($password);

            $user->setRoles([User::ROLE_USER]);

            $avatarFilename = FileRenamer::rename($avatar);

            copy(
                $avatar,
                $this->parameters->get('app.filesystem.images.users.avatars.path') . DIRECTORY_SEPARATOR . $avatarFilename
            );

            $user->setAvatar($avatarFilename);

            $manager->persist($user);

            $this->addReference('user-' . ($i + 1), $user);
        }

        $manager->flush();
    }

    /**
     * @param string $avatarFilename
     * @return string
     */
    private function getGenderFromAvatar(string $avatarFilename): string
    {
        $filename = pathinfo($avatarFilename, PATHINFO_BASENAME);

        $parts = explode('-', $filename);

        return $parts[1];
    }
}
