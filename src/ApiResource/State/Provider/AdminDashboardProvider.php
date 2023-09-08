<?php

declare(strict_types=1);

namespace App\ApiResource\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Dashboard;

/**
 * Class AdminDashboardProvider
 * @package App\ApiResource\State\Provider
 */
final readonly class AdminDashboardProvider implements ProviderInterface
{
    /**
     * @param Dashboard $dashboard
     */
    public function __construct(private Dashboard $dashboard)
    {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return array[]
     * @throws \JsonException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return [
            'data' => [
                'totals' => $this->dashboard->getTotals(),
                'most_recent_videos' => $this->dashboard->getMostRecentVideos(5),
                'most_commented_videos' => $this->dashboard->getMostCommentedVideos(5),
                'most_tagged_videos' => $this->dashboard->getMostTaggedVideos(5),
                'most_populous_tags' => $this->dashboard->getMostPopulousTags(5),
                'most_viewed_videos' => $this->dashboard->getMostViewedVideos(5),
                'most_populous_playlists' => $this->dashboard->getMostPopulousPlaylists(5),
                'highest_voted_videos' => $this->dashboard->getHighestVotedVideos(5),
                'most_active_users' => $this->dashboard->getMostActiveUsers(5),
                'latest_logins' => $this->dashboard->getLatestLogins(5),
                'charts' => [
                    'video_uploads_by_month' => $this->dashboard->getVideoUploadsByMonth(),
                    'categories_population' => $this->dashboard->getCategoriesPopulation(),
                ],
            ],
        ];
    }
}
