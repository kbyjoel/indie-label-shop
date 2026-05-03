<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DownloadToken;
use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DownloadToken>
 */
class DownloadTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DownloadToken::class);
    }

    public function findByTokenValue(string $tokenValue): ?DownloadToken
    {
        return $this->findOneBy(['tokenValue' => $tokenValue]);
    }

    public function findForOrderItemAndFormat(OrderItem $orderItem, string $format): ?DownloadToken
    {
        return $this->findOneBy(['orderItem' => $orderItem, 'format' => $format]);
    }
}
