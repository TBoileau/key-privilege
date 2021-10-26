<?php

declare(strict_types=1);

namespace App\Repository\Key;

use App\Entity\Company\Member;
use App\Entity\Key\Transaction;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Paginator<Transaction>
     */
    public function getTransactionsByEmployee(
        SalesPerson | Manager $employee,
        int $page,
        int $length,
        string $field,
        string $direction,
        ?string $filter
    ): Paginator {
        if ($employee instanceof SalesPerson) {
            /** @var SalesPerson $salesPerson */
            $salesPerson = $employee;
            $queryBuilder = $this->createQueryBuilderTransactionsBySalesPerson($salesPerson);
        } else {
            /** @var Manager $manager */
            $manager = $employee;
            $queryBuilder = $this->createQueryBuilderTransactionsByManager($manager);
        }

        if ($filter !== null) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('a.ownerName', ':filter'),
                    $queryBuilder->expr()->like('a.type', ':filter'),
                    $queryBuilder->expr()->like('a.companyName', ':filter'),
                )
            )->setParameter('filter', '%' . $filter . '%');
        }

        return new Paginator(
            $queryBuilder
                ->orderBy($field, $direction)
                ->setFirstResult(($page - 1) * $length)
                ->setMaxResults($length),
            true
        );
    }

    private function createQueryBuilderTransactionsBySalesPerson(SalesPerson $salesPerson): QueryBuilder
    {
        $customerAccountsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("account1.id")
            ->from(Customer::class, "customer")
            ->join("customer.account", "account1")
            ->join("customer.client", "client")
            ->where("client.member IN (:members)")
            ->getDQL();

        $queryBuilder = $this->createQueryBuilder("t")
            ->addSelect('a')
            ->addSelect("u")
            ->addSelect("w")
            ->join('t.account', 'a')
            ->join("a.user", "u")
            ->leftJoin("a.wallets", "w")
            ->setParameter("members", [$salesPerson->getMember()->getId()]);

        return $queryBuilder->andWhere($queryBuilder->expr()->in("a.id", $customerAccountsQueryBuilder));
    }

    public function createQueryBuilderTransactionsByManager(Manager $manager): QueryBuilder
    {
        $customerAccountsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("account1.id")
            ->from(Customer::class, "customer")
            ->join("customer.account", "account1")
            ->join("customer.client", "client")
            ->where("client.member IN (:members)")
            ->getDQL();

        $collaboratorAccountsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("account2.id")
            ->from(Collaborator::class, "collaborator")
            ->join("collaborator.account", "account2")
            ->where("collaborator.member IN (:members)")
            ->getDQL();

        $salesPersonAccountsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("account3.id")
            ->from(SalesPerson::class, "salesPerson")
            ->join("salesPerson.account", "account3")
            ->where("salesPerson.member IN (:members)")
            ->getDQL();

        $managerAccountsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("account4.id")
            ->from(Manager::class, "manager")
            ->join("manager.account", "account4")
            ->where("manager.member IN (:members)")
            ->getDQL();

        $membersAccountsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("account5.id")
            ->from(Member::class, "m2")
            ->join("m2.account", "account5")
            ->where("m2 IN (:members)")
            ->getDQL();

        $queryBuilder = $this->createQueryBuilder('t')
            ->addSelect("a")
            ->addSelect("u")
            ->addSelect("m")
            ->addSelect("w")
            ->join('t.account', 'a')
            ->leftJoin("a.user", "u")
            ->leftJoin("a.member", "m")
            ->leftJoin("a.wallets", "w")
            ->setParameter("members", $manager->getMembers()->map(fn (Member $member) => $member->getId())->toArray());

        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->in("a.id", $customerAccountsQueryBuilder),
                $queryBuilder->expr()->in("a.id", $collaboratorAccountsQueryBuilder),
                $queryBuilder->expr()->in("a.id", $salesPersonAccountsQueryBuilder),
                $queryBuilder->expr()->in("a.id", $managerAccountsQueryBuilder),
                $queryBuilder->expr()->in("a.id", $membersAccountsQueryBuilder)
            )
        );

        return $queryBuilder;
    }
}
