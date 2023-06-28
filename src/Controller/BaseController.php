<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Configurations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BaseController
 * @package App\Controller
 */
abstract class BaseController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em
     * @param ParameterBagInterface $parameters
     * @param Configurations $configurations
     * @param EventDispatcherInterface $dispatcher
     * @param TranslatorInterface $translator
     */
    public function __construct(
        protected EntityManagerInterface $em,
        protected ParameterBagInterface $parameters,
        protected Configurations $configurations,
        protected EventDispatcherInterface $dispatcher,
        protected TranslatorInterface $translator
    )
    {
    }
}
