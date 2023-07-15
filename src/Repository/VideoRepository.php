<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Video;
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
