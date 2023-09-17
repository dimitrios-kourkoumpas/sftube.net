<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class SubscriptionsController
 * @package App\Controller
 */
#[IsGranted(User::ROLE_USER)]
final class SubscriptionsController extends BaseController
{
    /**
     * @return Response
     */
    #[Route('/my-subscribers', name: 'app.subscribers.my', methods: [Request::METHOD_GET])]
    public function mySubscribers(): Response
    {
        $user = $this->getUser();

        return $this->render('subscriptions/my-subscribers.html.twig', [
            'subscribers' => $user->getSubscribers(),
        ]);
    }

    /**
     * @return Response
     */
    #[Route('/my-subscriptions', name: 'app.subscriptions.my', methods: [Request::METHOD_GET])]
    public function mySubscriptions(): Response
    {
        $user = $this->getUser();

        return $this->render('subscriptions/my-subscriptions.html.twig', [
            'subscriptions' => $user->getSubscriptions(),
        ]);
    }
}
