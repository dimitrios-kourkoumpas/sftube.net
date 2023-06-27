<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\DataReaderFactory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class AdminUserFixture
 * @package App\Fixtures
 */
final class AdminUserFixture extends Fixture
{
    private const DATA_FILE = 'admin.json';

    /**
     * @param ParameterBagInterface $parameters
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(
        private readonly ParameterBagInterface $parameters,
        private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $dataFile = $this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . self::DATA_FILE;

        $loader = new DataLoader(DataReaderFactory::create($dataFile));
        $data = $loader->loadData();

        $admin = new User();

        $admin->setFirstname($data['firstname']);
        $admin->setLastname($data['lastname']);
        $admin->setEmail($data['email']);

        $password = $this->hasher->hashPassword($admin, $data['password']);

        $admin->setPassword($password);
        $admin->setRoles([User::ROLE_ADMIN]);

        $manager->persist($admin);

        $manager->flush();
    }
}
