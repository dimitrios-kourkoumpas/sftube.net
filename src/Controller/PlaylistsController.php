<?php

namespace App\Controller;

use App\Entity\Playlist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PlaylistsController
 * @package App\Controller
 */
final class PlaylistsController extends BaseController
{
    /**
     * @return Response
     */
    #[Route('/playlists', name: 'app.playlists.index', methods: ['GET'])]
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
}
