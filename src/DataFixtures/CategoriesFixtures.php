<?php

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\DataReaderFactory;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class CategoriesFixtures
 * @package App\DataFixtures
 */
final class CategoriesFixtures extends Fixture
{
    private const DATA_FILE = 'categories.json';

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
        $categories = $loader->loadData();

        foreach ($categories as $cat) {
            $category = new Category();

            $category->setName($cat['name']);

            $manager->persist($category);

            $this->addReference('category-' . $category->getSlug(), $category);
        }

        $manager->flush();
    }
}
