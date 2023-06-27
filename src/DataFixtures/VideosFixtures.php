<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\DataReaderFactory;
use App\Entity\Video;
use App\Service\VideoExtractor\VideoExtractor;
use App\Util\FileRenamer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class VideosFixtures
 * @package App\DataFixtures
 */
final class VideosFixtures extends Fixture implements DependentFixtureInterface
{
    private const DATA_FILE = 'videos.json';

    /**
     * @param ParameterBagInterface $parameters
     * @param VideoExtractor $extractor
     * @param Filesystem $filesystem
     */
    public function __construct(
        private readonly ParameterBagInterface $parameters,
        private readonly VideoExtractor $extractor,
        private readonly Filesystem $filesystem
    )
    {
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        if (!$this->filesystem->exists($this->parameters->get('app.filesystem.images.videos.thumbnails.path') . DIRECTORY_SEPARATOR . Video::NOT_AVAILABLE)) {
            $this->filesystem->copy($this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . Video::NOT_AVAILABLE
                , $this->parameters->get('app.filesystem.images.videos.thumbnails.path') . DIRECTORY_SEPARATOR . Video::NOT_AVAILABLE
            );
        }

        $dataFile = $this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . self::DATA_FILE;

        $loader = new DataLoader(DataReaderFactory::create($dataFile));
        $videos = $loader->loadData();

        foreach ($videos as $i => $v) {
            if ($this->filesystem->exists($this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . $v['file'])) {
                $newFilename = FileRenamer::rename($v['file']);

                // simulate normal application flow, ie target the uploads directory
                $this->filesystem->copy(
                    $this->parameters->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . $v['file'],
                    $this->parameters->get('app.filesystem.videos.upload.path') . DIRECTORY_SEPARATOR . $newFilename
                );

                $video = new Video();

                $video->setTitle($v['title']);
                $video->setDescription($v['description']);
                $video->setViews(mt_rand(10,  50));

                $video->setCategory($this->getReference('category-' . $v['category']));

                foreach ($v['tags'] as $tag) {
                    $video->addTag($this->getReference('tag-' . $tag));
                }

                $video->setUser($this->getReference('user-' . mt_rand(1, UsersFixtures::MAX_USERS)));

                $video->setFilename($newFilename);
                $video->setExtractionMethod([Video::SLIDESHOW_EXTRACTION, Video::PREVIEW_EXTRACTION][mt_rand(0, 1)]);

                $this->extractor->extract($video);

                // hardcoded by default for DataFixtures
                $video->setPublished(true);

                $manager->persist($video);

                $this->addReference('video-' . $video->getSlug(), $video);
                $this->addReference('video-' . ($i + 1), $video);
            }
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
