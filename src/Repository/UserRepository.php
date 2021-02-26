<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\Manager;
use App\Entity\Member;
use App\Entity\SalesPerson;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<T>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return Paginator<User>
     */
    public function getPaginatedUsers(
        Manager|SalesPerson $employee,
        int $currentPage,
        int $limit,
        ?string $keywords
    ): Paginator {
        $queryBuilder = $this->createQueryBuilder("u")
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
                ->andWhere("m = :member")
                ->setParameter("member", $employee->getMember());
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
