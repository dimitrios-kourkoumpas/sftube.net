<?php

namespace App\Controller;

use App\Entity\Category;

/**
 * Class CategoriesController
 * @package App\Controller
 */
final class CategoriesController extends BaseController
{
    public function categories(int $max = 10)
    {
        $categories = $this->em->getRepository(Category::class)->findBy([], ['name' => 'ASC'], $max);

        return $this->render('categories/partials/_categories-sidebar.html.twig', [
            'categories' => $categories,
        ]);
    }
}
