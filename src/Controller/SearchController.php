<?php

namespace App\Controller;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
use Elastica\Query\Term;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SearchController
 * @package App\Controller
 */
final class SearchController extends BaseController
{
    /**
     * @param TransformedFinder $finder
     * @param Request $request
     * @return Response
     */
    #[Route('/search', name: 'app.search', methods: [Request::METHOD_GET])]
    public function search(TransformedFinder $finder, Request $request): Response
    {
        $term = $request->query->get('term');

        $query = new MultiMatch();

        $query->setQuery($term);
        $query->setFields([
            'title',
            'description',
            'category',
            'tags',
        ]);

        $filter = new BoolQuery();

        $filter->addMust(new Term(['published' => true]));
        $filter->addMust(new Term(['converted' => true]));

        $query = Query::create($query);

        $query->setPostFilter($filter);
        $query->setSort(['createdAt' => 'DESC']);

        $videos = $finder->find($query);

        return $this->render('search/search.html.twig', [
            'term' => $term,
            'videos' => $videos,
        ]);
    }

    /**
     * @param TransformedFinder $finder
     * @return Response
     */
    public function countSearchable(TransformedFinder $finder): Response
    {
        $query = new Query();

        $query = Query::create($query);

        $filter = new Query\BoolQuery();
        $filter->addMust(new Term(['published' => true]));
        $filter->addMust(new Term(['converted' => true]));

        $query->setPostFilter($filter);

        $count = $finder->findPaginated($query)->getNbResults();

        return $this->render('search/partials/_count.html.twig', [
            'count' => $count,
        ]);
    }
}
