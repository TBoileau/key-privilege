<?php

namespace App\Repository;

use App\Entity\Rules;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rules|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rules|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rules[]    findAll()
 * @method Rules[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<Rules>
 */
class RulesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rules::class);
    }

    public function getLastPublishedRules(): Rules
    {
        return $this->createQueryBuilder("r")
            ->where("r.publishedAt <= NOW()")
            ->setMaxResults(1)
            ->orderBy("r.publishedAt", "DESC")
            ->getQuery()
            ->getSingleResult();
    }
}
