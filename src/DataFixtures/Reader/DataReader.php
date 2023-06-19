<?php

namespace App\DataFixtures\Reader;


/**
 * Interface DataReader
 * @package App\DataFixtures\Reader
 */
interface DataReader
{
    /**
     * @return array
     */
    public function read(): array;
}
