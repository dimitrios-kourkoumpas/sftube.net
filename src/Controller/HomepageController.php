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
    #[Route('/', name: 'app.homepage', methods: [Request::METHOD_GET])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $perPage = $this->configurations->isSet('videos-per-page')
            ? $this->configurations->get('videos-per-page')
            : Video::PER_PAGE;

        $repository = $this->em->getRepository(Video::class);

        $total = $repository->count([]);

        $pages = (int) ceil($total / $perPage);

        $sort = $request->query->getString('sort', 'createdAt-desc');

        list($field, $direction) = explode('-', $sort);

        if (!in_array($field, ['createdAt', 'views']) || !in_array($direction, ['asc', 'desc'])) {
            $field = 'createdAt';
            $direction = 'desc';
        }

        // Doctrine Filter takes care of published and converted videos
        $videos = $repository->findBy([], [$field => $direction], $perPage, ($page - 1) * $perPage);

        return $this->render('homepage/index.html.twig', [
            'videos' => $videos,
            'pagination' => compact('page', 'pages'),
        ]);
    }
}
