<?php

namespace App\Controller;

use App\Service\Configurations;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
     */
    public function __construct(
        protected EntityManagerInterface $em,
        protected ParameterBagInterface $parameters,
        protected Configurations $configurations,
        protected EventDispatcherInterface $dispatcher
    )
    {
    }
}
