<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Message\ExtractVideoMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
     * @param Request $request
     * @param MessageBusInterface $bus
     * @return Response
     */
    #[Route('/upload', name: 'app.videos.upload', methods: ['GET', 'POST'])]
    #[IsGranted('upload')]
    public function upload(Request $request, MessageBusInterface $bus): Response
    {
        $video = new Video();

        $form = $this->createForm(VideoType::class, $video);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video->setUser($this->getUser());

            $this->em->persist($video);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('controller.videos.upload.flash.upload-successful'));

            $bus->dispatch(new ExtractVideoMessage($video->getId()));

            $this->addFlash('info', $this->translator->trans('controller.videos.upload.flash.video-queued'));

            return $this->redirectToRoute('app.homepage');
        }

        return $this->render('videos/upload.html.twig', [
            'form' => $form->createView(),
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
