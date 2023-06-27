<?php

declare(strict_types=1);

namespace App\DataFixtures\Reader;

/**
 * Class DataReaderFactory
 * @package App\DataFixtures\Reader
 */
final class DataReaderFactory
{
    private const JSON_FORMAT = 'json';

    /**
     * @param string $dataFile
     * @return DataReader
     */
    public static function create(string $dataFile): DataReader
    {
        $extension = pathinfo($dataFile, PATHINFO_EXTENSION);

        return match ($extension) {
            self::JSON_FORMAT => new JSONDataReader($dataFile),
            // TODO: cover more data file formats: CSV, YAML, XML etc
        };
    }
}
