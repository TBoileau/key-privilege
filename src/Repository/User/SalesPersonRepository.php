<?php

namespace App\Repository\User;

use App\Entity\User\SalesPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SalesPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SalesPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SalesPerson[]    findAll()
 * @method SalesPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<T>
 */
class SalesPersonRepository extends ServiceEntityRepository
{
    use UniqueEmailTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalesPerson::class);
    }
}
