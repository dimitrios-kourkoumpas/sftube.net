<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Video;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class VideosController
 * @package App\Controller\Admin
 */
#[IsGranted(User::ROLE_ADMIN)]
#[Route('/admin/videos')]
final class VideosController extends AdminCRUDController
{
    private const ENTITY = Video::class;

    /**
     * @return Response
     */
    #[Route('/', name: 'app.admin.videos.index', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        parent::init(self::ENTITY);

        return $this->render('admin/' . $this->inflections['slug'] . '/index.html.twig', [
            'meta' => $this->entityMetadata,
            'inflections' => $this->inflections,
        ]);
    }

    /**
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/datatable', name: 'app.admin.videos.datatable', methods: [Request::METHOD_GET])]
    public function datatable(): Response
    {
        $response = $this->getCollection(self::ENTITY);

        // TODO: hacky - improve this!!!
        return new Response(str_replace('webserver', 'localhost', $response['content']), $response['status']);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}', name: 'app.admin.videos.update', methods: [Request::METHOD_PATCH])]
    public function update(int $id, Request $request, ): Response
    {
        $response = $this->patch($id, $request, self::ENTITY);

        return new Response($response['content'], $response['status']);
    }

    /**
     * @param Video $video
     * @return Response
     */
    #[Route('/detail/{id}', name: 'app.admin.videos.detail', methods: [Request::METHOD_GET])]
    public function detail(#[MapEntity(expr: 'repository.findDisableFilter(id)')] Video $video): Response
    {
        parent::init(self::ENTITY);

        return $this->render('/admin/' . $this->inflections['slug'] . '/detail.html.twig', [
            'video' => $video,
            'inflections' => $this->inflections,
        ]);
    }
}
