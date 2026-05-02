<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ShopUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopUser>
 */
class ShopUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopUser::class);
    }

    public function findOneByEmail(string $email): ?ShopUser
    {
        return $this->findOneBy(['usernameCanonical' => strtolower($email)]);
    }
}
