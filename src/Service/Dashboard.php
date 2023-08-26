<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Playlist;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Dashboard
 * @package App\Service
 */
final readonly class Dashboard
{
    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return array
     */
    public function getTotals(): array
    {
        return [
            'totalVideos' => $this->getTotalVideos(),
            'totalUsers' => $this->getTotalUsers(),
            'totalCategories' => $this->getTotalCategories(),
            'totalTags' => $this->getTotalTags(),
            'totalPlaylists' => $this->getTotalPlaylists(),
            'totalComments' => $this->getTotalComments(),
        ];
    }

    /**
     * @param int $limit
     * @return Video[]
     */
    public function getMostRecentVideos(int $limit = 5): array
    {
        return $this->em->getRepository(Video::class)->getMostRecentVideos($limit);
    }

    /**
     * @param int $limit
     * @return Video[]
     */
    public function getMostCommentedVideos(int $limit = 5)
    {
        return $this->em->getRepository(Video::class)->getMostCommentedVideos($limit);
    }

    /**
     * @param int $limit
     * @return Video[]
     */
    public function getMostTaggedVideos(int $limit = 5): array
    {
        return $this->em->getRepository(Video::class)->getMostTaggedVideos($limit);
    }

    /**
     * @param int $limit
     * @return Tag[]
     */
    public function getMostPopulousTags(int $limit = 5): array
    {
        return $this->em->getRepository(Tag::class)->getMostPopulousTags($limit);
    }

    /**
     * @param int $limit
     * @return Video[]
     */
    public function getMostViewedVideos(int $limit = 5): array
    {
        return $this->em->getRepository(Video::class)->findBy([], ['views' => 'DESC', 'title' => 'ASC'], $limit);
    }

    /**
     * @param int $limit
     * @return Playlist[]
     */
    public function getMostPopulousPlaylists(int $limit = 5): array
    {
        return $this->em->getRepository(Playlist::class)->getMostPopulousPlaylists($limit);
    }

    /**
     * @return string
     * @throws \JsonException
     */
    public function getVideoUploadsByMonth(): string
    {
        $data = $this->em->getRepository(Video::class)->getVideoUploadsByMonth();

        $rtn = [];

        foreach ($data as $datum) {
            $obj = new \stdClass();

            $obj->month = $datum['date'];
            $obj->value = $datum['videos'];

            $rtn[] = $obj;
        }

        return \json_encode($rtn, JSON_THROW_ON_ERROR);
    }

    /**
     * @return string
     * @throws \JsonException
     */
    public function getCategoriesPopulation(): string
    {
        $data = $this->em->getRepository(Category::class)->getCategoriesPopulation();

        $rtn = [];

        foreach ($data as $datum) {
            $obj = new \stdClass();

            $obj->label = $datum['name'];
            $obj->value = $datum['videos'];

            $rtn[] = $obj;
        }

        return \json_encode($rtn, JSON_THROW_ON_ERROR);
    }

    public function getMostActiveUsers(int $limit = 5): array
    {
        return $this->em->getRepository(User::class)->getMostActiveUsers($limit);
    }

    /**
     * @param int $limit
     * @return User[]
     */
    public function getLatestLogins(int $limit = 5): array
    {
        return $this->em->getRepository(User::class)->findBy([], [
            'lastLogin' => 'DESC',
            'lastname' => 'ASC',
            'firstname' => 'ASC',
        ],
            $limit);
    }

    /**
     * @return int
     */
    private function getTotalVideos(): int
    {
        $filters = $this->em->getFilters();

        if ($filters->has('convertedAndPublishedVideosFilter') && $filters->isEnabled('convertedAndPublishedVideosFilter')) {
            $filters->disable('convertedAndPublishedVideosFilter');
        }

        return $this->em->getRepository(Video::class)->count([]);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getHighestVotedVideos(int $limit = 5): array
    {
        return $this->em->getRepository(Video::class)->getHighestVotedVideos($limit);
    }


    /**
     * @return int
     */
    private function getTotalUsers(): int
    {
        $filters = $this->em->getFilters();

        if ($filters->has('regularUsersFilter') && !$filters->isEnabled('regularUsersFilter')) {
            $filters->enable('regularUsersFilter');
        }

        return $this->em->getRepository(User::class)->count([]);
    }

    /**
     * @return int
     */
    private function getTotalCategories(): int
    {
        return $this->em->getRepository(Category::class)->count([]);
    }

    /**
     * @return int
     */
    private function getTotalTags(): int
    {
        return $this->em->getRepository(Tag::class)->count([]);
    }

    /**
     * @return int
     */
    private function getTotalPlaylists(): int
    {
        return $this->em->getRepository(Playlist::class)->count([]);
    }

    /**
     * @return int
     */
    private function getTotalComments(): int
    {
        return $this->em->getRepository(Comment::class)->count([]);
    }
}
