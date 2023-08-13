<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Class SuccessfulUserLoginEventListener
 * @package App\EventListener
 */
#[AsEventListener(event: LoginSuccessEvent::class)]
final readonly class SuccessfulUserLoginEventListener
{
    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param LoginSuccessEvent $event
     * @return void
     */
    public function __invoke(LoginSuccessEvent $event)
    {
        $user = $event->getUser();

        $user->setLastLogin(new \DateTime('now'));

        $this->em->persist($user);
        $this->em->flush();
    }
}
