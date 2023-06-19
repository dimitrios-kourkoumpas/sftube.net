<?php

namespace App\DataFixtures;

use App\DataFixtures\Loader\DataLoader;
use App\DataFixtures\Reader\JSONDataReader;
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
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $data = DataLoader::loadData(
            new JSONDataReader($this->parameterBag->get('app.fixtures.datapath') . DIRECTORY_SEPARATOR . self::DATA_FILE)
        );

        foreach ($data as $config) {
            $configuration = new Configuration();
            $configuration->setName($config['name']);
            $configuration->setValue($config['value']);

            $manager->persist($configuration);
        }

        $manager->flush();
    }
}
