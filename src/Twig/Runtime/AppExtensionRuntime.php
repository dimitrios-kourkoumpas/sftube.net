<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class AppExtensionRuntime
 * @package App\Twig\Runtime
 */
final readonly class AppExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * @param \DateTime $datetime
     * @param bool $full
     * @return string
     */
    public function timeElapsedSinceNowFilter(\DateTimeImmutable $datetime, bool $full = false): string
    {
        $then = $datetime;

        $now = new \DateTime('now');

        $diff = (array) $now->diff($then);

        $diff['w'] = floor($diff['d'] / 7);
        $diff['d'] -= $diff['w'] * 7;

        $string = array(
            'y' => $this->translator->trans('twig.filter.time-elapsed-since-now.year'),
            'm' => $this->translator->trans('twig.filter.time-elapsed-since-now.month'),
            'w' => $this->translator->trans('twig.filter.time-elapsed-since-now.week'),
            'd' => $this->translator->trans('twig.filter.time-elapsed-since-now.day'),
            'h' => $this->translator->trans('twig.filter.time-elapsed-since-now.hour'),
            'i' => $this->translator->trans('twig.filter.time-elapsed-since-now.minute'),
            's' => $this->translator->trans('twig.filter.time-elapsed-since-now.second'),
        );

        foreach ($string as $key => &$value) {
            if ($diff[$key]) {
                $value = $diff[$key] . ' ' . $value . ($diff[$key] > 1 ? 's' : '');
            } else {
                unset($string[$key]);
            }
        }

        unset($value);

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string
            ? implode(', ', $string) . ' ' . $this->translator->trans('twig.filter.time-elapsed-since-now.ago')
            : $this->translator->trans('twig.filter.time-elapsed-since-now.just-now');
    }

    /**
     * @param string $string
     * @return string
     */
    public function ucfirstFilter(string $string): string
    {
        return ucfirst($string);
    }

    /**
     * @link https://stackoverflow.com/questions/15188033/human-readable-file-size
     *
     * @param string $bytes
     * @param int $decimalDigits
     * @return string
     */
    public function humanReadableFileSizeFilter(string $bytes, int $decimalDigits = 2): string
    {
        $sizes = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimalDigits}f", $bytes / (1024 ** $factor)) . @$sizes[$factor];
    }

    /**
     * @param string $duration
     * @return string
     */
    public function humanReadableDurationFilter(string $duration): string
    {
        $hours = 0;

        if ($duration > 3600) {
            $hours = floor($duration / 3600);
        }

        $duration %= 3600;

        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT) . gmdate(':i:s', $duration);
    }
}
