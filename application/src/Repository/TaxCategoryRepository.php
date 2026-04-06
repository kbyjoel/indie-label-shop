<?php

namespace App\Repository;

use App\Entity\TaxCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaxCategory>
 *
 * @method TaxCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaxCategory|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method TaxCategory[]    findAll()
 * @method TaxCategory[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class TaxCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaxCategory::class);
    }

    public function findDefaultAlbum(): ?TaxCategory
    {
        return $this->findOneBy(['defaultForAlbum' => true]);
    }

    public function findDefaultTrack(): ?TaxCategory
    {
        return $this->findOneBy(['defaultForTrack' => true]);
    }

    public function findDefaultMerch(): ?TaxCategory
    {
        return $this->findOneBy(['defaultForMerch' => true]);
    }
}
