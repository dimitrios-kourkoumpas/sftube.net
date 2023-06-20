<?php

namespace App\Util;

/**
 * Class Slugger
 * @package App\Util
 */
final class Slugger
{
    /**
     * @link https://www.php.net/manual/en/transliterator.transliterate.php#110598
     *
     * @param string $string
     * @return string
     */
    public static function slugify(string $string): string
    {
        $string = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string);
        $string = preg_replace('/[-\s]+/', '-', $string);

        return trim($string, '-');
    }
}
