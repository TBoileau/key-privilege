<?php

namespace App\Repository\Company;

use App\Entity\Company\Member;
use App\Entity\User\Manager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function createQueryBuilderMembersByManager(Manager $employee): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder("m")
            ->orderBy("m.name", "asc");

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                "m.id",
                $employee->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
            )
        );

        return $queryBuilder;
    }
}
