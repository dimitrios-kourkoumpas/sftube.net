<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CommentsFixtures
 * @package App\DataFixtures
 */
final class CommentsFixtures extends Fixture implements DependentFixtureInterface
{
    private const MAX_COMMENTS = 5;

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        $i = 1;

        while ($this->hasReference('video-' . $i)) {
            $video = $this->getReference('video-' . $i);

            for ($j = 0; $j < mt_rand(1, self::MAX_COMMENTS); $j++) {
                $comment = new Comment();

                $comment->setComment($faker->sentences(mt_rand(1, 5), true));
                $comment->setUser($this->getReference('user-' . mt_rand(1, UsersFixtures::MAX_USERS)));
                $comment->setVideo($video);

                $manager->persist($comment);
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
