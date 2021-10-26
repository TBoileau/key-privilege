<?php

namespace App\Repository\User;

use App\Entity\Company\Member;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SalesPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SalesPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SalesPerson[]    findAll()
 * @method SalesPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<SalesPerson>
 */
class SalesPersonRepository extends ServiceEntityRepository
{
    use UniqueUserTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalesPerson::class);
    }

    public function createQueryBuilderSalesPersonsByManager(Manager $manager): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder("s")
            ->addSelect("m")
            ->join("s.member", "m")
            ->orderBy("s.firstName", "asc")
            ->addOrderBy("s.lastName", "asc");

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                "m.id",
                $manager->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
            )
        );

        return $queryBuilder;
    }
}
