<?php

namespace App\Repository\User;

use App\Entity\User\Manager;
use App\Entity\Company\Member;
use App\Entity\User\SalesPerson;
use App\Entity\User\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    use UniqueUserTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @return Paginator<Customer>
     */
    public function getPaginatedCustomers(
        Manager | SalesPerson $employee,
        int $currentPage,
        int $limit,
        ?string $keywords
    ): Paginator {
        $queryBuilder = $this->createQueryBuilder("u")
            ->addSelect("c")
            ->addSelect("m")
            ->join("u.client", "c")
            ->join("c.member", "m")
            ->andWhere("CONCAT(u.firstName, ' ', u.lastName, ' ', c.name) LIKE :keywords")
            ->setParameter("keywords", "%" . ($keywords ?? "") . "%")
            ->setFirstResult(($currentPage - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy("u.firstName", "asc")
            ->addOrderBy("u.lastName", "asc");

        if ($employee instanceof SalesPerson) {
            $queryBuilder
                ->andWhere("c.salesPerson = :salesPerson")
                ->setParameter("salesPerson", $employee);
        } else {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    "m.id",
                    $employee->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
                )
            );
        }

        return new Paginator($queryBuilder);
    }
}
