<?php

declare(strict_types=1);

namespace App\DataFixtures\Reader;

/**
 * Class JSONDataReader
 * @package App\DataFixtures\Reader
 */
final readonly class JSONDataReader implements DataReader
{
    /**
     * @param string $dataFile
     */
    public function __construct(private string $dataFile)
    {
    }

    /**
     * @return array
     */
    public function read(): array
    {
        return json_decode(file_get_contents($this->dataFile), true);
    }
}
