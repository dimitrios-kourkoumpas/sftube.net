<?php

namespace App\Util;

/**
 * Class FileRenamer
 * @package App\Util
 */
final class FileRenamer
{
    /**
     * @param string $filename
     * @return string
     */
    public static function rename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return uniqid(md5($filename), true) . '.' . $extension;
    }
}
