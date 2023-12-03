<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Video;
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
    #[Route('/tags', name: 'app.tags.index', methods: [Request::METHOD_GET])]
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

        return $this->render('tags/index.html.twig', [
            'tags' => $tags,
            'pagination' => compact('page', 'pages'),
        ]);
    }

    /**
     * @param string $slugs
     * @param Request $request
     * @return Response
     */
    #[Route('/tags/{slugs}', name: 'app.tags.view', requirements: ['slugs' => '.+'], methods: [Request::METHOD_GET])]
    public function view(string $slugs, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $perPage = $this->configurations->isSet('videos-per-page')
            ? (int) $this->configurations->get('videos-per-page')
            : Video::PER_PAGE;

        $slugs = explode('/', $slugs);

        $tagRepository = $this->em->getRepository(Tag::class);

        $tags = $tagRepository->findTagsForSlugs($slugs);

        $videoRepository = $this->em->getRepository(Video::class);

        $videos = $videoRepository->findVideosForTags($tags, $page, $perPage);
        $total = $videoRepository->countVideosForTags($tags);

        $pages = (int) ceil($total / $perPage);

        $URLFragment = $request->getPathInfo();

        $title = ucwords(implode(' / ', $slugs));

        return $this->render('tags/view.html.twig', [
            'title' => $title,
            'videos' => $videos,
            'pagination' => compact('page', 'pages', 'URLFragment')
        ]);
    }
}
