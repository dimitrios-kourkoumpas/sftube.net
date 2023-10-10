<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/add-remove-subscriber', name: 'app.subscriptions.add-remove-subscriber', methods: [Request::METHOD_POST])]
    public function addRemoveSubscriber(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();

            $targetUser = $this->em->getRepository(User::class)->find($request->request->get('subscriber_id'));

            $operation = $request->request->get('operation');

            $method = $operation . 'Subscriber';

            $targetUser->$method($user);

            $this->em->persist($targetUser);
            $this->em->flush();

            return $this->json([
                'success' => true,
            ]);
        }

        return $this->json([
            'success' => false,
        ], Response::HTTP_BAD_REQUEST);
    }
}
