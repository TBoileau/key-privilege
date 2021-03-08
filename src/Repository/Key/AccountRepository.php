<?php

declare(strict_types=1);

namespace App\Repository\Key;

use App\Entity\Company\Member;
use App\Entity\Key\Account;
use App\Entity\User\Manager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T
 * @extends ServiceEntityRepository<T>
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function createQueryBuilderAccountByManager(Manager $manager): QueryBuilder
    {
        return $this->createQueryBuilder("a")
            ->addSelect("c")
            ->join("a.company", "c")
            ->where("c.id IN (:members)")
            ->setParameter(
                "members",
                $manager->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
            );
    }
}
