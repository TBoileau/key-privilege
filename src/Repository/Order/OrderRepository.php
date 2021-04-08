<?php

namespace App\Repository\Order;

use App\Entity\Company\Member;
use App\Entity\Order\Order;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<T>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return array<int, Order>
     */
    public function getOrdersByMemberEmployee(SalesPerson | Manager $employee): array
    {
        $queryBuilder = $this->createQueryBuilder("o")
            ->join("o.user", "u")
            ->orderBy("o.createdAt", 'desc');

        if ($employee instanceof SalesPerson) {
            $subQuery = $this->_em->createQueryBuilder()
                ->select("c.id")
                ->from(Customer::class, "c")
                ->join("c.client", "cl")
                ->where("cl.salesPerson = :employee")
                ->getDQL();

            $queryBuilder->setParameter("employee", $employee);
        } else {
            $subQuery = $this->_em->createQueryBuilder()
                ->select("c.id")
                ->from(Customer::class, "c")
                ->join("c.client", "cl")
                ->join("cl.member", "m");

            $subQuery = $subQuery
                ->where($subQuery->expr()->in(
                    "m.id",
                    $employee->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
                ))
                ->getDQL();
        }

        return $queryBuilder
            ->andWhere($queryBuilder->expr()->in("u.id", $subQuery))
            ->getQuery()
            ->getResult();
    }
}
