<?php

namespace App\Repository\Shop;

use App\Entity\Shop\Universe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Universe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Universe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Universe[]    findAll()
 * @method Universe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<T>
 */
class UniverseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Universe::class);
    }
}
