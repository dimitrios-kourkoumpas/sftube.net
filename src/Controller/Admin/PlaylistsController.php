<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Playlist;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class PlaylistsController
 * @package App\Controller\Admin
 */
#[IsGranted(User::ROLE_ADMIN)]
#[Route('/admin/playlists')]
final class PlaylistsController extends AdminCRUDController
{
    private const ENTITY = Playlist::class;

    /**
     * @return Response
     */
    #[Route('/', name: 'app.admin.playlists.index', methods: [Request::METHOD_GET])]
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
    #[Route('/datatable', name: 'app.admin.playlists.datatable', methods: [Request::METHOD_GET])]
    public function datatable(): Response
    {
        $response = $this->getCollection(self::ENTITY);

        return new Response($response['content'], $response['status']);
    }

    /**
     * @param Playlist $playlist
     * @return Response
     */
    #[Route('/detail/{id}', name: 'app.admin.playlists.detail', methods: [Request::METHOD_GET])]
    public function detail(Playlist $playlist): Response
    {
        parent::init(self::ENTITY);

        return $this->render('admin/' . $this->inflections['slug'] . '/detail.html.twig', [
            'playlist' => $playlist,
            'inflections' => $this->inflections,
        ]);
    }
}
