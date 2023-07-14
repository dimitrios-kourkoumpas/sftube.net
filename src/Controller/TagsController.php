<?php

namespace App\Controller;

use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagsController
 * @package App\Controller
 */
final class TagsController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/tags', name: 'app.tags.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $perPage = $this->configurations->isSet('tags-per-page')
            ? (int) $this->configurations->get('tags-per-page')
            : Tag::PER_PAGE;

        $repository = $this->em->getRepository(Tag::class);

        $total = $repository->count([]);

        $tags = $repository->findBy([], ['name' => 'ASC'], $perPage, ($page - 1) * $perPage);

        $pages = (int) ceil($total / $perPage);

        $URLFragment = $request->getPathInfo();

        return $this->render('tags/index.html.twig', [
            'tags' => $tags,
            'pagination' => compact('page', 'pages', 'URLFragment'),
        ]);
    }
}
