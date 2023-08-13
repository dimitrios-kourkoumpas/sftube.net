<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class DashboardController
 * @package App\Controller\Admin
 */
#[IsGranted(User::ROLE_ADMIN)]
#[Route('/admin')]
final class DashboardController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route('/dashboard', name: 'app.admin.dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard/dashboard.html.twig', []);
    }
}
