<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserRegistration;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class RegistrationController
 * @package App\Controller
 */
final class RegistrationController extends BaseController
{
    #[Route('/register', name: 'app.register', methods: ['GET', 'POST'])]
    #[IsGranted('register')]
    public function register(Request $request, UserRegistration $registration, Security $security): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration->register($user);

            $this->addFlash('success', $this->translator->trans('controller.registration.register.flash.registration-successful'));

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
