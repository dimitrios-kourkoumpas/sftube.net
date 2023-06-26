<?php

namespace App\Controller;

use App\Entity\Video;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VideosController
 * @package App\Controller
 */
final class VideosController extends BaseController
{

    /**
     * @param int $max
     * @return Response
     */
    public function recent(int $max = 3): Response
    {
        $videos = $this->em->getRepository(Video::class)->findBy([], ['createdAt' => 'DESC'], $max);

        return $this->render('videos/partials/_recent.html.twig', [
            'videos' => $videos,
        ]);
    }
}
