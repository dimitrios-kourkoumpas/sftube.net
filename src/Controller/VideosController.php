<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Video;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class VideosController
 * @package App\Controller
 */
final class VideosController extends BaseController
{
    /**
     * @param Video $video
     * @return Response
     */
    #[Route('/watch/{id}/{slug}', name: 'app.videos.watch', methods: ['GET'])]
    public function watch(Video $video): Response
    {
        // increment views
        $video->setViews($video->getViews() + 1);

        $this->em->persist($video);
        $this->em->flush();

        return $this->render('videos/watch.html.twig', [
            'video' => $video,
        ]);
    }

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
