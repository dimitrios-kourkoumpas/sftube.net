<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Playlist;
use App\Entity\Video;
use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function save(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Category $category
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countVideosForCategory(Category $category): int
    {
        $qb = $this->createQueryBuilder('v');

        $qb->select('COUNT(v) AS total');
        $qb->where('v.category = :category');
        $qb->setParameter('category', $category);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Category $category
     * @param int $page
     * @param int $perPage
     * @return Video[]
     */
    public function findVideosForCategory(Category $category, int $page, int $perPage)
    {
        $qb = $this->createQueryBuilder('v');

        $qb->select('v');
        $qb->where('v.category = :category');
        $qb->setParameter('category', $category);
        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);
        $qb->orderBy('v.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $tags
     * @param int $page
     * @param int $perPage
     * @return Video[]
     */
    public function findVideosForTags(array $tags, int $page, int $perPage): array
    {
        $qb = $this->getFindForTagsQueryBuilder($tags);

        $qb->setFirstResult(($page - 1) * $perPage);
        $qb->setMaxResults($perPage);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $tags
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countVideosForTags(array $tags): int
    {
        $qb = $this->getFindForTagsQueryBuilder($tags);

        $qb->select('COUNT(v.id) AS total');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Playlist $playlist
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countVideosForPlaylist(Playlist $playlist): int
    {
        $qb = $this->createQueryBuilder('v');

        $qb->select('COUNT(v.id)');
        $qb->innerJoin('v.playlists', 'p');
        $qb->where('p = :playlist');
        $qb->setParameter('playlist', $playlist);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Playlist $playlist
     * @param int $page
     * @param int $limit
     * @return Video[]
     */
    public function findVideosForPlaylist(Playlist $playlist, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('v');

        $qb->select('v');
        $qb->innerJoin('v.playlists', 'p');
        $qb->where('p = :playlist');
        $qb->setParameter('playlist', $playlist);
        $qb->setMaxResults($limit);
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->orderBy('v.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $tags
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getFindForTagsQueryBuilder(array $tags)
    {
        $qb = $this->createQueryBuilder('v');

        $qb->innerJoin('v.tags', 'tags');

        foreach ($tags as $i => $tag) {
            if ($i === 0) {
                $qb->where('tags = :tag');
                $qb->setParameter('tag', $tag);
            }

            $qb->andWhere('tags = :tag');
            $qb->setParameter('tag', $tag);
        }

        return $qb;
    }

    /**
     * @param int $id
     * @return Video|null
     */
    public function findDisableFilter(int $id): ?Video
    {
        $filters = $this->_em->getFilters();

        if ($filters->has('convertedPublishedFilter') && $filters->isEnabled('convertedPublishedFilter')) {
            $filters->disable('convertedPublishedFilter');
        }

        return $this->find($id);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getMostRecentVideos(int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $queryBuilder->select(['v.id', 'v.title', 'v.thumbnail', 'v.createdAt']);
        $queryBuilder->orderBy('v.createdAt', 'DESC');
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getMostCommentedVideos(int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $queryBuilder->select(['v.id', 'v.title', 'v.thumbnail', 'COUNT(c.id) AS comments']);
        $queryBuilder->leftJoin('v.comments', 'c');
        $queryBuilder->groupBy('v.id');
        $queryBuilder->orderBy('comments', 'DESC');
        $queryBuilder->addOrderBy('v.title', 'ASC');
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getMostTaggedVideos(int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $queryBuilder->select(['v.id', 'v.title', 'v.thumbnail', 'COUNT(t.id) AS count']);
        $queryBuilder->leftJoin('v.tags', 't');
        $queryBuilder->groupBy('v.id');
        $queryBuilder->orderBy('count', 'DESC');
        $queryBuilder->addOrderBy('v.title', 'ASC');
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getVideoUploadsByMonth(): array
    {
        $sql = 'SELECT CONCAT(YEAR(`created_at`), "-", MONTH(`created_at`)) AS `date`, COUNT(`video`.`id`) AS `videos`';
        $sql .= ' FROM `video` GROUP BY MONTH(`created_at`), YEAR(`created_at`)';
        $sql .= ' ORDER BY YEAR(`created_at`) ASC, MONTH(`created_at`) ASC;';

        $connection = $this->_em->getConnection();

        $statement = $connection->prepare($sql);

        return $statement->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param int $limit
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getHighestVotedVideos(int $limit): array
    {
        $sql = 'SELECT `video`.`id`, `video`.`title`, `video`.`thumbnail`,
((SELECT COUNT(`vote`.`id`) FROM `vote` WHERE `video`.`id` = `vote`.`video_id` AND `vote`.`vote` = \'' . Vote::UP . '\') * 100) / COUNT(`vote`.`id`) AS `percentage`
FROM `video`
LEFT JOIN `vote` ON `video`.`id` = `vote`.`video_id`
GROUP BY `video`.`id`
ORDER BY `percentage` DESC, `video`.`title` ASC LIMIT ' . $limit;

        $connection = $this->_em->getConnection();

        $statement = $connection->prepare($sql);

        return $statement->executeQuery()->fetchAllAssociative();
    }


//    /**
//     * @return Video[] Returns an array of Video objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Video
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
