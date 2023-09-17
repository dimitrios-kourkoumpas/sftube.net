<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Video;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class CommentsController
 * @package App\Controller
 */
final class CommentsController extends BaseController
{
    /**
     * @param Video $video
     * @param Request $request
     * @return Response
     */
    #[Route('/videos/{id}/comment', name: 'app.videos.comment', methods: [Request::METHOD_POST])]
    #[IsGranted('comment', 'video')]
    public function comment(Video $video, Request $request): Response
    {
        if ($request->isXmlHttpRequest() && $request->isMethod(Request::METHOD_POST)) {
            $user = $this->getUser();

            $comment = new Comment();

            $comment->setUser($user);
            $comment->setComment($request->request->get('comment'));
            $comment->setVideo($video);

            $this->em->persist($comment);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
            ]);
        } else {
            return new JsonResponse([
                'success' => false,
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    /**
     * @param Video $video
     * @param Request $request
     * @return Response
     */
    #[Route('/videos/{id}/comments', name: 'app.videos.comments', methods: [Request::METHOD_GET])]
    public function comments(Video $video, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->render('comments/partials/_comments.html.twig', [
                'video' => $video,
            ]);
        }

        return new Response(null, Response::HTTP_FORBIDDEN);

    }
}
