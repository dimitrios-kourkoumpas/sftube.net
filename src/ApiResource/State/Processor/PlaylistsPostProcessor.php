<?php

declare(strict_types=1);

namespace App\ApiResource\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PlaylistsPostProcessor
 * @package App\ApiResource\State\Processor
 */
final readonly class PlaylistsPostProcessor implements ProcessorInterface
{
    /**
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(private EntityManagerInterface $em, private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $data->setUser($user);

        $this->em->persist($data);
        $this->em->flush();
    }
}
