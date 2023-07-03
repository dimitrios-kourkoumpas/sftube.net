<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class VotesFixtures
 * @package App\DataFixtures
 */
final class VotesFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $i = 1;

        while ($this->hasReference('video-' . $i)) {
            $video = $this->getReference('video-' . $i);

            for ($j = 0; $j < mt_rand(3, 5); $j++) {
                $user = $this->getReference('user-' . mt_rand(1, UsersFixtures::MAX_USERS));

                if ($video->hasVoted($user)) {
                    continue;
                }

                $vote = new Vote();

                $vote->setType([Vote::UP, Vote::DOWN][mt_rand(0, 1)]);
                $vote->setVideo($video);
                $vote->setUser($user);

                $manager->persist($vote);
            }

            $i++;
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            VideosFixtures::class,
        ];
    }
}
