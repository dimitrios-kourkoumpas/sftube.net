<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Util\Slugger;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class AdminCRUDController
 * @package App\Controller\Admin
 */
abstract class AdminCRUDController extends AbstractController
{
    private const BASE_URL = 'http://webserver/api/';

    /**
     * @var array $inflections
     */
    protected array $inflections = [];

    /**
     * @var array $entityMetadata
     */
    protected array $entityMetadata = [];

    /**
     * @param EntityManagerInterface $em
     * @param HttpClientInterface $httpClient
     * @param RequestStack $requestStack
     */
    public function __construct(private readonly EntityManagerInterface $em, private readonly HttpClientInterface $httpClient, private readonly RequestStack $requestStack)
    {
    }

    /**
     * @param string $entity
     * @return array
     * @throws TransportExceptionInterface
     */
    protected function getCollection(string $entity): array
    {
        $this->init($entity);

        return $this->sendRequest(Request::METHOD_GET, self::BASE_URL . $this->inflections['slug']);
    }

    /**
     * @param int $id
     * @param string $entity
     * @return array
     * @throws TransportExceptionInterface
     */
    public function getItem(int $id, string $entity): array
    {
        $this->init($entity);

        return $this->sendRequest(Request::METHOD_GET, self::BASE_URL . $this->inflections['slug'] . '/' . $id);
    }

    /**
     * @param Request $request
     * @param string $entity
     * @return array
     * @throws TransportExceptionInterface
     */
    protected function post(Request $request, string $entity): array
    {
        $this->init($entity);

        return $this->sendRequest(Request::METHOD_POST, self::BASE_URL . $this->inflections['slug'], [
            'body' => $request->getContent(true),
        ]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param string $entity
     * @return array
     * @throws TransportExceptionInterface
     */
    protected function patch(int $id, Request $request, string $entity): array
    {
        $this->init($entity);

        return $this->sendRequest(Request::METHOD_PATCH, self::BASE_URL . $this->inflections['slug'] . '/' . $id, [
            'body' => $request->getContent(true),
        ]);
    }

    /**
     * @param int $id
     * @param string $entity
     * @return array
     * @throws TransportExceptionInterface
     */
    protected function delete(int $id, string $entity): array
    {
        $this->init($entity);

        return $this->sendRequest(Request::METHOD_DELETE, self::BASE_URL . $this->inflections['slug'] . '/' . $id);
    }

    /**
     * @param string $entity
     * @return void
     */
    private function getInflections(string $entity): void
    {
        $entityPlainName = substr($entity, strrpos($entity, '\\') + 1);

        $inflector = InflectorFactory::create()
            ->withPluralRules(null)
            ->withSingularRules(null)
            ->build();

        $entityPluralName = $inflector->pluralize($entityPlainName);
        $entityLowerCase = strtolower($entityPlainName);
        $entitySlug = Slugger::slugify($entityPluralName);

        $this->inflections = [
            'singular' => $entityPlainName,
            'plural' => $entityPluralName,
            'lower' => $entityLowerCase,
            'slug' => $entitySlug,
        ];
    }

    /**
     * @param string $entity
     * @return void
     */
    private function getEntityMetadata(string $entity): void
    {
        $metadata = $this->em->getClassMetadata($entity);

        $this->entityMetadata = $metadata->fieldMappings;
    }

    /**
     * @param string $entity
     * @return void
     */
    protected function init(string $entity): void
    {
        $this->getInflections($entity);
        $this->getEntityMetadata($entity);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array
     * @throws TransportExceptionInterface
     */
    private function sendRequest(string $method, string $url, array $options = []): array
    {
        $options['headers'] = [
            'Content-Type' => $method === Request::METHOD_PATCH
                ? 'application/merge-patch+json'
                : 'application/json',
            'Accept' => 'application/json',
        ];

        $options['auth_bearer'] = $this->requestStack->getSession()->get('jwt');

        try {
            $response = $this->httpClient->request($method, $url, $options);

            $content = $response->getContent();
            $status = $response->getStatusCode();
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            return [
                'status' => $e->getCode(),
                'content' => $response->getContent(throw: false),
            ];
        }

        return [
            'content' => $content,
            'status' => $status,
        ];
    }
}
