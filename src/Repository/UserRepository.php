<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<T>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return Paginator<User>
     */
    public function getPaginatedUsers(int $currentPage, int $limit, ?string $keywords): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder("u")
                ->where("CONCAT(u.firstName, ' ', u.lastName) LIKE :keywords")
                ->setParameter("keywords", "%" . ($keywords ?? "") . "%")
                ->setFirstResult(($currentPage - 1) * $limit)
                ->setMaxResults($limit)
                ->orderBy("u.firstName", "asc")
                ->addOrderBy("u.lastName", "asc")
        );
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
}
