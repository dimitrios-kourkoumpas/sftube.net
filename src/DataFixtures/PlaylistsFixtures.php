<?php

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\DataReaderFactory;
use App\Entity\Playlist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class PlaylistsFixtures
 * @package App\DataFixtures
 */
final class PlaylistsFixtures extends Fixture implements DependentFixtureInterface
{
    private const DATA_FILE = 'playlists.json';

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
        $dataFile = $this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . self::DATA_FILE;

        $loader = new DataLoader(DataReaderFactory::create($dataFile));
        $playlists = $loader->loadData();

        foreach ($playlists as $p) {
            $playlist = new Playlist();

            $playlist->setName($p['name']);

            if (isset($p['private'])) {
                $playlist->setPrivate($p['private']);
            }

            $playlist->setUser($this->getReference('user-' . mt_rand(1, UsersFixtures::MAX_USERS)));

            foreach ($p['videos'] as $video) {
                $playlist->addVideo($this->getReference('video-' . $video));
            }

            $manager->persist($playlist);
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
