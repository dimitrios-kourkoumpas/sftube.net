<?php

declare(strict_types=1);

namespace App\ApiResource\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Service\UserRegistration;

/**
 * Class UserRegistrationProcessor
 * @package App\ApiResource\State\Processor
 */
final readonly class UserRegistrationProcessor implements ProcessorInterface
{
    /**
     * @param UserRegistration $registration
     */
    public function __construct(private UserRegistration $registration)
    {
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return User|void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof User) {
            $this->registration->register($data);

            return $data;
        }
    }
}
