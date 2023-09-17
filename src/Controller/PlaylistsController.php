<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Playlist;
use App\Entity\User;
use App\Entity\Video;
use App\Form\PlaylistType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class PlaylistsController
 * @package App\Controller
 */
final class PlaylistsController extends BaseController
{
    /**
     * @return Response
     */
    #[Route('/playlists', name: 'app.playlists.index', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $repository = $this->em->getRepository(Playlist::class);

        $playlists = $repository->findBy(['private' => false], ['name' => 'ASC']);

        // deal with logged user's private playlists
        $user = $this->getUser();

        if ($user instanceof UserInterface) {
            $userPrivatePlaylists = $user->getPlaylists(private: true)->toArray();

            $playlists = array_merge($playlists, $userPrivatePlaylists);

            usort($playlists, function (Playlist $p1, Playlist $p2) {
                if ($p1->getName() === $p2->getName()) {
                    return 0;
                }

                return $p1->getName() < $p2->getName() ? -1 : 1;
            });
        }

        return $this->render('playlists/index.html.twig', [
            'playlists' => $playlists,
        ]);
    }

    /**
     * @param Playlist $playlist
     * @param Request $request
     * @return Response
     */
    #[Route('/playlists/edit/{id}', name: 'app.playlists.edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted('edit', 'playlist')]
    public function edit(Playlist $playlist, Request $request): Response
    {
        $form = $this->createForm(PlaylistType::class, $playlist);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($playlist);
            $this->em->flush();

            return $this->redirectToRoute('app.playlists.my');
        }

        return $this->render('playlists/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/playlists/delete/{id}', name: 'app.playlists.delete', methods: [Request::METHOD_POST])]
    #[IsGranted('delete', 'playlist')]
    public function delete(Request $request, Playlist $playlist): Response
    {
        if ($this->isCsrfTokenValid('delete' . $playlist->getId(), $request->request->get('_token'))) {
            $this->em->remove($playlist);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('controller.playlists.delete.flash.playlist-deleted-successfully'));

            return $this->redirectToRoute('app.playlists.my');
        }
    }

    /**
     * @param Playlist $playlist
     * @param Request $request
     * @return Response
     */
    #[Route('/playlists/{id}/{slug}', name: 'app.playlists.view', methods: [Request::METHOD_GET])]
    public function view(Playlist $playlist, Request $request): Response
    {
        if ($playlist->isPrivate()) {
            $user = $this->getUser();

            if (!$user instanceof UserInterface || !$playlist->isOwner($user)) {
                throw $this->createAccessDeniedException();
            }
        }

        $page = $request->query->getInt('page', 1);

        $perPage = $this->configurations->isSet('videos-per-page')
            ? (int)$this->configurations->get('videos-per-page')
            : Video::PER_PAGE;

        $videoRepository = $this->em->getRepository(Video::class);

        $total = $videoRepository->countVideosForPlaylist($playlist);

        $pages = (int)ceil($total / $perPage);

        $videos = $videoRepository->findVideosForPlaylist($playlist, $page, $perPage);

        $URLFragment = $request->getPathInfo();

        return $this->render('playlists/view.html.twig', [
            'playlist' => $playlist,
            'videos' => $videos,
            'pagination' => compact('page', 'pages', 'URLFragment'),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/playlists/create', name: 'app.playlists.create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted(User::ROLE_USER)]
    public function create(Request $request): Response
    {
        $playlist = new Playlist();

        $form = $this->createForm(PlaylistType::class, $playlist);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $playlist->setUser($this->getUser());

            $this->em->persist($playlist);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('controller.playlists.create.flash.playlist-created'));

            return $this->redirectToRoute('app.playlists.my');
        }

        return $this->render('playlists/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return Response
     */
    #[Route('/my-playlists', name: 'app.playlists.my', methods: [Request::METHOD_GET])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function my(): Response
    {
        $user = $this->getUser();

        // get all user's playlists
        $playlists = $user->getPlaylists(private: null);

        return $this->render('playlists/my.html.twig', [
            'playlists' => $playlists,
        ]);
    }
}
