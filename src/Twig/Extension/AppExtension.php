<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppExtension
 * @package App\Twig\Extension
 */
final class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('timeElapsedSinceNow', [AppExtensionRuntime::class, 'timeElapsedSinceNowFilter']),
            new TwigFilter('ucfirst', [AppExtensionRuntime::class, 'ucfirstFilter']),
            new TwigFilter('humanReadableFileSize', [AppExtensionRuntime::class, 'humanReadableFileSizeFilter']),
            new TwigFilter('humanReadableDuration', [AppExtensionRuntime::class, 'humanReadableDurationFilter']),
        ];
    }
}
