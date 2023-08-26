<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class UsersController
 * @package App\Controller\Admin
 */
#[IsGranted(User::ROLE_ADMIN)]
#[Route('/admin/users')]
final class UsersController extends AdminCRUDController
{
    private const ENTITY = User::class;

    /**
     * @return Response
     */
    #[Route('/', name: 'app.admin.users.index', methods: [Request::METHOD_GET])]
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
    #[Route('/datatable', name: 'app.admin.users.datatable', methods: [Request::METHOD_GET])]
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
    #[Route('/{id}', name: 'app.admin.users.update', methods: [Request::METHOD_PATCH])]
    public function update(int $id, Request $request, ): Response
    {
        $response = $this->patch($id, $request, self::ENTITY);

        return new Response($response['content'], $response['status']);
    }

    /**
     * @param User $user
     * @return Response
     */
    #[Route('/detail/{id}', name: 'app.admin.users.detail', methods: [Request::METHOD_GET])]
    public function detail(User $user): Response
    {
        parent::init(self::ENTITY);

        return $this->render('/admin/' . $this->inflections['slug'] . '/detail.html.twig', [
            'user' => $user,
            'inflections' => $this->inflections,
        ]);
    }
}
