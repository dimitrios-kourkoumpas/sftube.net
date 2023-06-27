<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Video;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomepageController
 * @package App\Controller
 */
final class HomepageController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/', name: 'app.homepage', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $perPage = $this->configurations->isSet('videos-per-page')
            ? $this->configurations->get('videos-per-page')
            : Video::PER_PAGE;

        $repository = $this->em->getRepository(Video::class);

        $total = $repository->count([]);

        $pages = (int) ceil($total / $perPage);

        $videos = $repository->findBy([], ['createdAt' => 'DESC'], $perPage, ($page - 1) * $perPage);

        return $this->render('homepage/index.html.twig', [
            'videos' => $videos,
            'pagination' => compact('page', 'pages'),
        ]);
    }
}
