<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\DataReaderFactory;
use App\Entity\Configuration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class ConfigurationsFixtures
 * @package App\DataFixtures
 */
final class ConfigurationsFixtures extends Fixture
{
    private const DATA_FILE = 'configurations.json';

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
        $data = $loader->loadData();

        foreach ($data as $config) {
            $configuration = new Configuration();
            $configuration->setName($config['name']);
            $configuration->setValue($config['value']);

            $manager->persist($configuration);
        }

        $manager->flush();
    }
}
