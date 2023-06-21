<?php

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\JSONDataReader;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class TagsFixtures
 * @package App\DataFixtures
 */
final class TagsFixtures extends Fixture
{
    private const DATA_FILE = 'tags.json';

    /**
     * @param ParameterBagInterface $parameters
     */
    public function __construct(private readonly ParameterBagInterface $parameters)
    {
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $tags = DataLoader::loadData(
            new JSONDataReader(
                $this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . self::DATA_FILE
            )
        );

        foreach ($tags as $t) {
            $tag = new Tag();

            $tag->setName($t['name']);

            $manager->persist($tag);

            $this->addReference('tag-' . $tag->getSlug(), $tag);
        }

        $manager->flush();
    }
}
