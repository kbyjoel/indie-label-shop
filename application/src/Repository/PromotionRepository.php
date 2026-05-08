<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Repository\PromotionRepositoryInterface;
use Sylius\Resource\Model\ResourceInterface;

/**
 * @extends ServiceEntityRepository<Promotion>
 *
 * @implements PromotionRepositoryInterface<Promotion>
 */
class PromotionRepository extends ServiceEntityRepository implements PromotionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    public function findActive(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('p')
            ->where('p.archivedAt IS NULL')
            ->andWhere('p.startsAt IS NULL OR p.startsAt <= :now')
            ->andWhere('p.endsAt IS NULL OR p.endsAt >= :now')
            ->setParameter('now', $now)
            ->orderBy('p.priority', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByName(string $name): array
    {
        return $this->findBy(['name' => $name]);
    }

    public function findActiveByChannel(ChannelInterface $channel): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('p')
            ->innerJoin('p.channels', 'c')
            ->where('c = :channel')
            ->andWhere('p.archivedAt IS NULL')
            ->andWhere('p.startsAt IS NULL OR p.startsAt <= :now')
            ->andWhere('p.endsAt IS NULL OR p.endsAt >= :now')
            ->setParameter('channel', $channel)
            ->setParameter('now', $now)
            ->orderBy('p.priority', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findActiveNonCouponBasedByChannel(ChannelInterface $channel): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('p')
            ->innerJoin('p.channels', 'c')
            ->where('c = :channel')
            ->andWhere('p.couponBased = false')
            ->andWhere('p.archivedAt IS NULL')
            ->andWhere('p.startsAt IS NULL OR p.startsAt <= :now')
            ->andWhere('p.endsAt IS NULL OR p.endsAt >= :now')
            ->setParameter('channel', $channel)
            ->setParameter('now', $now)
            ->orderBy('p.priority', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function createPaginator(array $criteria = [], array $sorting = []): iterable
    {
        throw new \RuntimeException('createPaginator is not implemented for PromotionRepository.');
    }

    public function add(ResourceInterface $resource): void
    {
        $this->getEntityManager()->persist($resource);
        $this->getEntityManager()->flush();
    }

    public function remove(ResourceInterface $resource): void
    {
        $this->getEntityManager()->remove($resource);
        $this->getEntityManager()->flush();
    }
}
