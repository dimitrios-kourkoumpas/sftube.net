<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Video;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoriesController
 * @package App\Controller
 */
final class CategoriesController extends BaseController
{
    /**
     * @param Category $category
     * @param Request $request
     * @return Response
     */
    #[Route('/category/{slug}', name: 'app.category.view', methods: ['GET'])]
    public function view(Category $category, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $perPage = $this->configurations->isSet('videos-per-page')
            ? (int) $this->configurations->get('videos-per-page')
            : Video::PER_PAGE;

        $repository = $this->em->getRepository(Video::class);

        $total = $repository->countVideosForCategory($category);
        $videos = $repository->findVideosForCategory($category, $page, $perPage);

        $pages = (int) ceil($total / $perPage);

        $URLFragment = $request->getPathInfo();

        return $this->render('categories/view.html.twig', [
            'category' => $category,
            'videos' => $videos,
            'pagination' => compact('page', 'pages', 'URLFragment')
        ]);
    }

    /**
     * @param int $max
     * @return Response
     */
    public function categories(int $max = 10): Response
    {
        $categories = $this->em->getRepository(Category::class)->findBy([], ['name' => 'ASC'], $max);

        return $this->render('categories/partials/_categories-sidebar.html.twig', [
            'categories' => $categories,
        ]);
    }
}
