<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Resource\Model\ResourceInterface;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @implements OrderRepositoryInterface<Order>
 */
class OrderRepository extends ServiceEntityRepository implements OrderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function countByCustomer(CustomerInterface $customer): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.customer = :customer')
            ->andWhere('o.state != :state')
            ->setParameter('customer', $customer)
            ->setParameter('state', 'cart')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countByCustomerAndCoupon(CustomerInterface $customer, PromotionCouponInterface $coupon): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.customer = :customer')
            ->andWhere('o.promotionCoupon = :coupon')
            ->andWhere('o.state != :state')
            ->setParameter('customer', $customer)
            ->setParameter('coupon', $coupon)
            ->setParameter('state', 'cart')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countPlacedOrders(): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findLatest(int $count): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findLatestCart(): ?BaseOrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findOneByNumber(string $number): ?BaseOrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findOneByTokenValue(string $tokenValue): ?BaseOrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartById(mixed $id): ?BaseOrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartsNotModifiedSince(\DateTimeInterface $terminalDate, ?int $limit = null): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function createCartQueryBuilder(): QueryBuilder
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findAllExceptCarts(): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function createListQueryBuilder(): QueryBuilder
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function createCriteriaAwareSearchListQueryBuilder(?array $criteria): QueryBuilder
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function createByCustomerIdCriteriaAwareQueryBuilder(?array $criteria, string $customerId): QueryBuilder
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function createByCustomerAndChannelIdQueryBuilder(mixed $customerId, mixed $channelId): QueryBuilder
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findOrderById(mixed $id): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findByCustomer(CustomerInterface $customer): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findForCustomerStatistics(CustomerInterface $customer): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findOneForPayment(mixed $id): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findOneByNumberAndCustomer(string $number, CustomerInterface $customer): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartByChannel(mixed $id, ChannelInterface $channel): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findLatestCartByChannelAndCustomer(ChannelInterface $channel, CustomerInterface $customer): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findLatestNotEmptyCartByChannelAndCustomer(ChannelInterface $channel, CustomerInterface $customer): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function getTotalSalesForChannel(ChannelInterface $channel): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function getTotalPaidSalesForChannel(ChannelInterface $channel): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function getTotalPaidSalesForChannelInPeriod(ChannelInterface $channel, \DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function countFulfilledByChannel(ChannelInterface $channel): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function countPaidByChannel(ChannelInterface $channel): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function countPaidForChannelInPeriod(ChannelInterface $channel, \DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findLatestInChannel(int $count, ChannelInterface $channel): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findOrdersUnpaidSince(\DateTimeInterface $terminalDate, ?int $limit = null): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartForSummary(mixed $id): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartForAddressing(mixed $id): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartForSelectingShipping(mixed $id): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartForSelectingPayment(mixed $id): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartByTokenValue(string $tokenValue): ?BaseOrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findCartByTokenValueAndChannel(string $tokenValue, ChannelInterface $channel): ?BaseOrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function getGroupedTotalPaidSalesForChannelInPeriod(ChannelInterface $channel, \DateTimeInterface $startDate, \DateTimeInterface $endDate, array $groupBy): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function countGroupedPaidForChannelInPeriod(ChannelInterface $channel, \DateTimeInterface $startDate, \DateTimeInterface $endDate, array $groupBy): array
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function findOneWithCompletedCheckout(string $tokenValue): ?OrderInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function countNewByChannel(ChannelInterface $channel): int
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function createPaginator(array $criteria = [], array $sorting = []): iterable
    {
        throw new \RuntimeException('Not implemented.');
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
