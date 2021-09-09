<?php

declare(strict_types=1);

namespace App\Repository\Key;

use App\Entity\Company\Member;
use App\Entity\Key\Account;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T
 * @extends ServiceEntityRepository<Account>
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @return array<int, Account>
     */
    public function getClientsAccountByEmployee(SalesPerson | Manager $employee): array
    {
        $members = [$employee->getMember()->getId()];

        if ($employee instanceof Manager) {
            $members = $employee->getMembers()->map(fn (Member $member) => $member->getId())->toArray();
        }

        $customerAccountsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("account1.id")
            ->from(Customer::class, "customer")
            ->join("customer.account", "account1")
            ->join("customer.client", "client")
            ->where("client.member IN (:members)")
            ->getDQL();

        $queryBuilder = $this->createQueryBuilder("a")
            ->addSelect("u")
            ->addSelect("w")
            ->join("a.user", "u")
            ->leftJoin("a.wallets", "w")
            ->setParameter("members", $members);

        return $queryBuilder
            ->andWhere($queryBuilder->expr()->in("a.id", $customerAccountsQueryBuilder))
            ->getQuery()
            ->getResult();
    }

    public function createQueryBuilderAccountByManagerForTransfer(Manager $manager): QueryBuilder
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

        $queryBuilder = $this->createQueryBuilder("a")
            ->addSelect("u")
            ->addSelect("m")
            ->addSelect("w")
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

    public function createQueryBuilderAccountByManagerForPurchase(Manager $manager): QueryBuilder
    {
        return $this->createQueryBuilder("a")
            ->addSelect("m")
            ->join("a.member", "m")
            ->where("m IN (:members)")
            ->setParameter(
                "members",
                $manager->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
            );
    }
}
