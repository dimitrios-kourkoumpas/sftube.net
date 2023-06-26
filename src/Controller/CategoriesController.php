<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CategoriesController
 * @package App\Controller
 */
final class CategoriesController extends BaseController
{
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
