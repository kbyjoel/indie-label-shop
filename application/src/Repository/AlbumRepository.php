<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Band;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Album>
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    public function createOnlinePaginatedQuery(?Band $band = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.artwork', 'aw')
            ->addSelect('aw')
            ->where('a.status = :status')
            ->setParameter('status', 'online')
            ->orderBy('a.releaseDate', 'DESC')
        ;

        if (null !== $band) {
            $qb->andWhere('a.band = :band')->setParameter('band', $band);
        }

        return $qb;
    }

    /** @return Album[] */
    public function findLatestOnline(int $limit = 6, ?Band $band = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.artwork', 'aw')
            ->addSelect('aw')
            ->where('a.status = :status')
            ->setParameter('status', 'online')
            ->orderBy('a.releaseDate', 'DESC')
            ->setMaxResults($limit)
        ;

        if (null !== $band) {
            $qb->andWhere('a.band = :band')->setParameter('band', $band);
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneBySlug(string $slug): ?Album
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.artwork', 'aw')
            ->addSelect('aw')
            ->leftJoin('a.band', 'b')
            ->addSelect('b')
            ->leftJoin('a.tracklists', 'tl')
            ->addSelect('tl')
            ->leftJoin('tl.track', 't')
            ->addSelect('t')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('tl.position', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
