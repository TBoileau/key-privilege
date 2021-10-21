<?php

namespace App\Repository\User;

use App\Entity\Company\Member;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use UniqueUserTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @codeCoverageIgnore
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @return Paginator<SalesPerson|Collaborator|Manager>
     */
    public function getPaginatedEmployees(Manager $manager, int $currentPage, int $limit, mixed $keywords): Paginator
    {
        $collaboratorsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("c.id")
            ->from(Collaborator::class, "c")
            ->where("c.member IN (:members)")
            ->getDQL();
        $managersQueryBuilder = $this->_em->createQueryBuilder()
            ->select("m.id")
            ->from(Manager::class, "m")
            ->where("m.member IN (:members)")
            ->getDQL();
        $salesPersonsQueryBuilder = $this->_em->createQueryBuilder()
            ->select("s.id")
            ->from(SalesPerson::class, "s")
            ->where("s.member IN (:members)")
            ->getDQL();

        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("u")
            ->from(User::class, "u")
            ->andWhere("CONCAT(u.firstName, ' ', u.lastName) LIKE :keywords")
            ->andWhere("u != :manager")
            ->setParameter("manager", $manager)
            ->setParameter("keywords", "%" . ($keywords ?? "") . "%")
            ->setFirstResult(($currentPage - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy("u.firstName", "asc")
            ->addOrderBy("u.lastName", "asc");

        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->in("u.id", $collaboratorsQueryBuilder),
                $queryBuilder->expr()->in("u.id", $salesPersonsQueryBuilder),
                $queryBuilder->expr()->in("u.id", $managersQueryBuilder)
            )
        )->setParameter("members", $manager->getMembers()->map(fn (Member $member) => $member->getId())->toArray());

        return new Paginator($queryBuilder);
    }
}
