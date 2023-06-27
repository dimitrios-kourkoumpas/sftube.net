<?php

declare(strict_types=1);

namespace App\DataFixtures\Loader;

use App\DataFixtures\Reader\DataReader;

/**
 * Class DataLoader
 * @package App\DataFixtures\Loader
 */
final class DataLoader
{
    /**
     * @param DataReader $reader
     */
    public function __construct(private readonly DataReader $reader)
    {
    }

    /**
     * @return array
     */
    public function loadData(): array
    {
        return $this->reader->read();
    }
}
