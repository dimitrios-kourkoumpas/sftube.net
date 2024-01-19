<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Configuration;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class ConfigurationsController
 * @package App\Controller\Admin
 */
#[IsGranted(User::ROLE_ADMIN)]
#[Route('/admin/configurations')]
final class ConfigurationsController extends AdminCRUDController
{
    private const ENTITY = Configuration::class;

    /**
     * @return Response
     */
    #[Route('/', name: 'app.admin.configurations.index', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        parent::init(self::ENTITY);

        return $this->render('admin/' . $this->inflections['slug'] . '/index.html.twig', [
            'meta' => $this->entityMetadata,
            'inflections'  => $this->inflections,
        ]);
    }

    /**
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/datatable', name: 'app.admin.configurations.datatable', methods: [Request::METHOD_GET])]
    public function datatable(): Response
    {
        $response = $this->getCollection(self::ENTITY);

        return new Response($response['content']);
    }

    /**
     * @param int $id
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}', name: 'app.admin.configurations.get', methods: [Request::METHOD_GET])]
    public function getOne(int $id): Response
    {
        $response = $this->getItem($id, self::ENTITY);

        return new Response($response['content']);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/', name: 'app.admin.configurations.create', methods: [Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $response = $this->post($request, self::ENTITY);

        return new Response($response['content'], $response['status']);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}', name: 'app.admin.configurations.update', methods: [Request::METHOD_PATCH])]
    public function update(int $id, Request $request): Response
    {
        $response = $this->patch($id, $request, self::ENTITY);

        return new Response($response['content']);
    }

    /**
     * @param int $id
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}', name: 'app.admin.configurations.delete', methods: [Request::METHOD_DELETE])]
    public function remove(int $id): Response
    {
        $response = $this->delete($id, self::ENTITY);

        return new Response($response['content'], $response['status']);
    }
}
