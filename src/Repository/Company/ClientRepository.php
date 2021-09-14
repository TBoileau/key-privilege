<?php

namespace App\Repository\Company;

use App\Entity\Company\Client;
use App\Entity\Company\Member;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @param SalesPerson|Manager $employee
     */
    public function createQueryBuilderClientsByEmployee(SalesPerson | Manager $employee): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder("c")
            ->addSelect("m")
            ->join("c.member", "m")
            ->orderBy("c.name", "asc");

        if ($employee instanceof Manager) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    "m.id",
                    $employee->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
                )
            );
        } else {
            $queryBuilder->where("m = :member")->setParameter("member", $employee->getMember());
        }

        return $queryBuilder;
    }

    /**
     * @return Paginator<Client>
     */
    public function getPaginatedClients(
        Manager $employee,
        int $currentPage,
        int $limit,
        ?string $keywords
    ): Paginator {
        $queryBuilder = $this->createQueryBuilder("c")
            ->addSelect("s")
            ->addSelect("m")
            ->leftJoin("c.salesPerson", "s")
            ->join("c.member", "m")
            ->andWhere("c.name LIKE :keywords")
            ->setParameter("keywords", "%" . ($keywords ?? "") . "%")
            ->setFirstResult(($currentPage - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy("c.name", "asc");

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                "m.id",
                $employee->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
            )
        );

        return new Paginator($queryBuilder);
    }
}
