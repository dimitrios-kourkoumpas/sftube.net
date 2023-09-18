<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProfileController
 * @package App\Controller
 */
final class ProfileController extends BaseController
{
    /**
     * @param User $user
     * @return Response
     */
    #[Route('/profile/{id}', name: 'app.user.profile', methods: [Request::METHOD_GET])]
    public function profile(User $user): Response
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            $this->addFlash('danger', $this->translator->trans('controller.profile.profile.flash.can-not-access-profile'));

            return $this->redirectToRoute('app.homepage');
        }

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
