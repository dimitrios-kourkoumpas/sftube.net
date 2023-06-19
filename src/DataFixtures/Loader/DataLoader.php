<?php

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
     * @return array
     */
    public static function loadData(DataReader $reader): array
    {
        return $reader->read();
    }
}
