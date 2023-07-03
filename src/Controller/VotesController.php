<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use App\Entity\Vote;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class VotesController
 * @package App\Controller
 */
final class VotesController extends BaseController
{
    /**
     * @param Video $video
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/videos/{id}/vote', name: 'app.videos.vote', methods: [Request::METHOD_POST])]
    #[IsGranted(User::ROLE_USER)]
    public function vote(Video $video, Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest() && $request->isMethod(Request::METHOD_POST)) {
            $user = $this->getUser();

            $vote = new Vote();

            $vote->setUser($user);
            $vote->setVideo($video);
            $vote->setVote($request->request->get('vote'));

            $this->em->persist($vote);
            $this->em->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(null, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * @param Video $video
     * @param Request $request
     * @return Response
     */
    #[Route('/videos/{id}/votes', name: 'app.videos.votes', methods: [Request::METHOD_GET])]
    public function votes(Video $video, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        return $this->render('votes/partials/_votes-row.html.twig', [
            'video' => $video,
        ]);
    }
}
