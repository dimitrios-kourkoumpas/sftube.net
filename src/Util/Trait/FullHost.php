<?php

namespace App\Util\Trait;

use Symfony\Component\Routing\RouterInterface;

/**
 * Trait FullHost
 * @package App\Util\Trait
 */
trait FullHost
{
    /**
     * @param RouterInterface $router
     * @return string
     */
    private function getFullHost(RouterInterface $router): string
    {
        $context = $router->getContext();

        $scheme = $context->getScheme();
        $host = $context->getHost();
        $httpPort = $context->getHttpPort();

        $port = $httpPort === 80 ? '' : ':' . $httpPort;

        return $scheme . '://' . $host . $port . '/';
    }
}
