<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class SubscriptionsFixtures
 * @package App\DataFixtures
 */
final class SubscriptionsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(User::class);

        $users = $repository->findRegularUsers();

        $usersCount = count($users);

        for ($i = 1; $i < UsersFixtures::MAX_USERS; $i++) {
            for ($i = 0; $i < $usersCount; $i++) {
                $user = $users[$i];

                for ($j = 0; $j < random_int(1, $usersCount); $j++) {
                    $randomUser = $users[random_int(1, $usersCount - 1)];

                    if ($user->getId() === $randomUser->getId() || $user->hasSubscription($randomUser)) {
                        $j--;

                        continue;
                    }

                    $user->addSubscription($randomUser);

                    $manager->persist($user);
                }
            }
        }

        $manager->flush();
    }
}
