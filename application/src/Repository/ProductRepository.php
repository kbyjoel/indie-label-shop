<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function createEnabledPaginatedQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.image', 'i')
            ->addSelect('i')
            ->andWhere('p NOT INSTANCE OF ' . Album::class)
            ->andWhere('p.enabled = true')
            ->orderBy('p.id', 'DESC')
        ;
    }

    public function findOneBySlug(string $slug, string $locale): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.image', 'i')
            ->addSelect('i')
            ->leftJoin('p.variants', 'v')
            ->addSelect('v')
            ->leftJoin('v.optionValues', 'ov')
            ->addSelect('ov')
            ->leftJoin('ov.translations', 'ovt')
            ->addSelect('ovt')
            ->leftJoin('p.translations', 't')
            ->addSelect('t')
            ->leftJoin('p.options', 'o')
            ->addSelect('o')
            ->leftJoin('o.values', 'oval')
            ->addSelect('oval')
            ->andWhere('p NOT INSTANCE OF ' . Album::class)
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('t.locale = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
