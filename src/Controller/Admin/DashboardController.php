<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class DashboardController
 * @package App\Controller\Admin
 */
#[IsGranted(User::ROLE_ADMIN)]
#[Route('/admin')]
final class DashboardController extends AbstractController
{
    /**
     * @param Dashboard $dashboard
     * @return Response
     * @throws \JsonException
     */
    #[Route('/dashboard', name: 'app.admin.dashboard', methods: [Request::METHOD_GET])]
    public function dashboard(Dashboard $dashboard): Response
    {
        return $this->render('admin/dashboard/dashboard.html.twig', [
            'inflections' => [
                'title' => 'Dashboard',
                'plural' => 'Dashboard', // hack..
                'slug' => 'dashboard',
            ],
            'data' => [
                'totals' => $dashboard->getTotals(),
                'most_recent_videos' => $dashboard->getMostRecentVideos(5),
                'most_commented_videos' => $dashboard->getMostCommentedVideos(5),
                'most_tagged_videos' => $dashboard->getMostTaggedVideos(5),
                'most_populous_tags' => $dashboard->getMostPopulousTags(5),
                'most_viewed_videos' => $dashboard->getMostViewedVideos(5),
                'most_populous_playlists' => $dashboard->getMostPopulousPlaylists(5),
                'highest_voted_videos' => $dashboard->getHighestVotedVideos(5),
                'most_active_users' => $dashboard->getMostActiveUsers(5),
                'latest_logins' => $dashboard->getLatestLogins(5),
                'charts' => [
                    'video_uploads_by_month' => $dashboard->getVideoUploadsByMonth(),
                    'categories_population' => $dashboard->getCategoriesPopulation(),
                ],
            ],
        ]);
    }
}
