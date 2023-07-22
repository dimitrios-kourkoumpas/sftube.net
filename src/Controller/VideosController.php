<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use App\Form\VideoType;
use App\Message\ExtractVideoMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function PHPUnit\Framework\returnArgument;

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
     * @param Video $video
     * @param Request $request
     * @return Response
     */
    #[Route('/edit/{id}', name: 'app.videos.edit', methods: ['GET', 'POST'])]
    public function edit(Video $video, Request $request): Response
    {
        $form = $this->createForm(VideoType::class, $video);

        // con not change these anymore :)
        $form->remove('videoFile');
        $form->remove('extractionMethod');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($video);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('controller.videos.edit.flash.video-updated'));

            return $this->redirectToRoute('app.videos.my');
        }

        return $this->render('videos/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Video $video
     * @return Response
     */
    #[Route('/video/{id}/delete', name: 'app.videos.delete', methods: ['POST'])]
    #[IsGranted('delete', 'video')]
    public function delete(Request $request, Video $video): Response
    {
        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {
            $this->em->remove($video);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('controller.videos.delete.flash.video-deleted'));

            return $this->redirectToRoute('app.homepage');
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/my-videos', name: 'app.videos.my', methods: ['GET'])]
    #[IsGranted(User::ROLE_USER)]
    public function myVideos(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $perPage = $this->configurations->isSet('videos-per-page')
            ? (int) $this->configurations->get('videos-per-page')
            : Video::PER_PAGE;

        $user = $this->getUser();

        $repository = $this->em->getRepository(Video::class);

        $total = $repository->count(['user' => $user]);

        $pages = (int) ceil($total / $perPage);

        $videos = $repository->findBy(['user' => $user], ['createdAt' => 'DESC'], $perPage, ($page - 1) * $perPage);

        $URLFragment = $request->getPathInfo();

        return $this->render('videos/my-videos.html.twig', [
            'videos' => $videos,
            'pagination' => compact('page', 'pages', 'URLFragment'),
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

    /**
     * @param int $max
     * @return Response
     */
    #[IsGranted(User::ROLE_USER)]
    public function fromSubscriptions(int $max = 3): Response
    {
        $subscriptions = $this->getUser()->getSubscriptions();

        $videos = [];

        foreach ($subscriptions as $subscription) {
            foreach ($subscription->getVideos() as $video) {
                $videos[] = $video;
            }
        }

        usort($videos, function (Video $v1, Video $v2) {
            if ($v1->getCreatedAt() === $v2->getCreatedAt()) {
                return 0;
            }

            return $v1->getCreatedAt() > $v2->getCreatedAt() ? -1 : 1;
        });

        $videos = array_slice($videos, 0, $max);

        return $this->render('videos/partials/_from-subscriptions.html.twig', [
            'videos' => $videos,
        ]);
    }
}
