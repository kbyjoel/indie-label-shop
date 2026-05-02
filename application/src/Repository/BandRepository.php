<?php

namespace App\Repository;

use App\Entity\Band;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Band>
 */
class BandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Band::class);
    }

    /** @return Band[] */
    public function findAllOnline(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.image', 'i')
            ->addSelect('i')
            ->where('b.status = :status')
            ->setParameter('status', 'online')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlug(string $slug): ?Band
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.image', 'i')
            ->addSelect('i')
            ->leftJoin('b.members', 'm')
            ->addSelect('m')
            ->where('b.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
