<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ConfigurationRepository;

/**
 * Class Configurations
 * @package App\Service
 */
final class Configurations
{
    /**
     * @var \stdClass|null $configurations
     */
    private ?\stdClass $configurations = null;

    /**
     * @param ConfigurationRepository $repository
     */
    public function __construct(ConfigurationRepository $repository)
    {
        $configurations = $repository->findAll();

        $this->configurations = new \stdClass();

        foreach ($configurations as $configuration) {
            $this->configurations->{$configuration->getName()} = $configuration->getValue();
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isSet(string $name): bool
    {
        return isset($this->configurations->{$name});
    }

    /**
     * @param string $name
     * @return string
     */
    public function get(string $name): string
    {
        return $this->configurations->{$name};
    }
}