<?php

namespace App\Repository\Order;

use App\Entity\Order\Line;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Line|null find($id, $lockMode = null, $lockVersion = null)
 * @method Line|null findOneBy(array $criteria, array $orderBy = null)
 * @method Line[]    findAll()
 * @method Line[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<Line>
 */
class LineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Line::class);
    }
}
