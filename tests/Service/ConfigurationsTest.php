<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;
use App\Service\Configurations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConfigurationsTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testConfigurationExists(): void
    {
        $service = $this->createConfigurationsService();

        self::assertTrue($service->isSet('allow-video-uploads'));
    }

    /**
     * @throws Exception
     */
    public function testConfigurationDoesntExist(): void
    {
        $service = $this->createConfigurationsService();

        self::assertFalse($service->isSet('allow-some-nonsense'));
    }

    /**
     * @throws Exception
     */
    public function testGetConfigurationValue(): void
    {
        $service = $this->createConfigurationsService();

        self::assertTrue($service->isSet('videos-per-page'));
        self::assertEquals(16, $service->get('videos-per-page'));

    }

    /**
     * @throws Exception
     */
    private function createConfigurationsService(): Configurations
    {
        $repository = $this->createMock(ConfigurationRepository::class);

        $configurations = [
            (new Configuration())->setName('allow-video-uploads')->setValue('1'),
            (new Configuration())->setName('videos-per-page')->setValue('16'),
        ];

        $repository->method('findAll')->willReturn($configurations);

        return new Configurations($repository);
    }
}
