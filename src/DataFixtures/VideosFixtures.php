<?php

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\DataReaderFactory;
use App\Entity\Video;
use App\Util\FileRenamer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class VideosFixtures
 * @package App\DataFixtures
 */
final class VideosFixtures extends Fixture implements DependentFixtureInterface
{
    private const DATA_FILE = 'videos.json';

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
        $videos = $loader->loadData();

        foreach ($videos as $i => $v) {
            $video = new Video();

            $video->setTitle($v['title']);
            $video->setDescription($v['description']);
            $video->setViews(mt_rand(10,  50));

            $video->setCategory($this->getReference('category-' . $v['category']));

            foreach ($v['tags'] as $tag) {
                $video->addTag($this->getReference('tag-' . $tag));
            }

            $video->setUser($this->getReference('user-' . mt_rand(1, UsersFixtures::MAX_USERS)));

            $video->setFilename(FileRenamer::rename($v['file']));
            $video->setExtractionMethod([Video::SLIDESHOW_EXTRACTION, Video::PREVIEW_EXTRACTION][mt_rand(0, 1)]);

            $manager->persist($video);

            $this->addReference('video-' . $video->getSlug(), $video);
            $this->addReference('video-' . ($i + 1), $video);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            CategoriesFixtures::class,
            TagsFixtures::class,
            UsersFixtures::class,
        ];
    }
}
