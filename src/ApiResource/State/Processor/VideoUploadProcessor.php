<?php

declare(strict_types=1);

namespace App\ApiResource\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Video;
use App\Message\ExtractVideoMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class VideoUploadProcessor
 * @package App\ApiResource\State\Processor
 */
final readonly class VideoUploadProcessor implements ProcessorInterface
{
    /**
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param MessageBusInterface $messageBus
     */
    public function __construct(private EntityManagerInterface $em, private Security $security, private MessageBusInterface $messageBus)
    {
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return void|Video
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();

        if ($user instanceof UserInterface) {
            if ($data instanceof Video) {
                $data->setUser($user);

                $this->em->persist($data);
                $this->em->flush();

                $this->messageBus->dispatch(new ExtractVideoMessage($data->getId()));

                return $data;
            }
        }
    }
}
