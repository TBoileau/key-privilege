<?php

namespace App\Repository\Shop;

use App\Entity\Shop\Category;
use App\Entity\Shop\Filter;
use App\Entity\Shop\Product;
use App\Entity\Shop\Universe;
use App\Entity\User\Employee;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return array<Product>
     */
    public function getLastProducts(User $user): array
    {
        /** @var array<array-key, class-string> $userTraits */
        $userTraits = class_uses($user);
        if (in_array(Employee::class, $userTraits) || $user->getAccount()->getBalance() === 0) {
            return $this->createQueryBuilder("p")
                ->addSelect("RAND() as HIDDEN rand")
                ->addSelect("b")
                ->addSelect("c")
                ->join("p.brand", "b")
                ->join("p.category", "c")
                ->leftJoin("c.lastProduct", "lp")
                ->setMaxResults(4)
                ->where("p.active = true")
                ->orderBy("rand", "desc")
                ->getQuery()
                ->getResult();
        }

        $subQuery = $this->createQueryBuilder('p2')
            ->select('MAX(p2.id)')
            ->join('p2.category', 'c2')
            ->where('p2.amount <= :balance')
            ->groupBy('c2.id')
            ->getQuery()
            ->getDQL();

        $queryBuilder = $this->createQueryBuilder("p")
            ->addSelect("b")
            ->addSelect("c")
            ->join("p.brand", "b")
            ->join("p.category", "c")
            ->where("p.active = true")
            ->setParameter('balance', $user->getAccount()->getBalance())
            ->orderBy('p.amount', 'desc')
            ->setMaxResults(4);

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in('p.id', $subQuery)
        );

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function getMinAmount(): int
    {
        return $this->createQueryBuilder("p")
            ->select("p.amount")
            ->where("p.active = true")
            ->orderBy("p.amount", "asc")
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getMaxAmount(): int
    {
        return $this->createQueryBuilder("p")
            ->select("p.amount")
            ->where("p.active = true")
            ->orderBy("p.amount", "desc")
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Paginator<Product>
     */
    public function getPaginatedProducts(
        int $page,
        int $limit,
        string $sort,
        ?Category $category,
        ?Universe $universe,
        Filter $filter
    ): Paginator {
        $queryBuilder = $this->createQueryBuilder("p")
            ->addSelect("b")
            ->addSelect("c")
            ->join("p.brand", "b")
            ->join("p.category", "c")
            ->leftJoin("c.lastProduct", "lp")
            ->andWhere('p.active = true')
            ->andWhere("p.amount >= :min")
            ->setParameter("min", $filter->min)
            ->andWhere("p.amount <= :max")
            ->setParameter("max", $filter->max)
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);

        $this->filterProducts($queryBuilder, $category, $universe, $filter);

        switch ($sort) {
            case "amount-asc":
                $queryBuilder->orderBy("p.amount", "asc");
                break;
            case "amount-desc":
                $queryBuilder->orderBy("p.amount", "desc");
                break;
            case "name-asc":
                $queryBuilder->orderBy("p.name", "asc");
                break;
            case "name-desc":
                $queryBuilder->orderBy("p.name", "desc");
                break;
            default:
                $queryBuilder->orderBy("lp.id", "desc")->orderBy("p.id", "desc");
                break;
        }

        return new Paginator($queryBuilder);
    }

    private function filterProducts(
        QueryBuilder $queryBuilder,
        ?Category $category,
        ?Universe $universe,
        Filter $filter
    ): void {
        if ($category !== null) {
            $queryBuilder
                ->andWhere("c.left >= :left")
                ->andWhere("c.right <= :right")
                ->setParameter("left", $category->getLeft())
                ->setParameter("right", $category->getRight());
        }

        if ($universe !== null && $category === null) {
            /** @var array<array-key, Category> $categories */
            $categories = $this->_em->createQueryBuilder()
                ->select('c2')
                ->from(Category::class, 'c2')
                ->join('c2.universes', 'u2')
                ->where('u2 = :universe')
                ->setParameter("universe", $universe)
                ->getQuery()
                ->getResult();

            /** @var array<array-key, Expr> $expressions */
            $expressions = [];

            foreach ($categories as $parentCategory) {
                $expressions[] = $queryBuilder->expr()->andX(
                    sprintf("c.left >= :left_%d", $parentCategory->getId()),
                    sprintf("c.right <= :right_%d", $parentCategory->getId())
                );
                $queryBuilder
                    ->setParameter(sprintf("left_%d", $parentCategory->getId()), $parentCategory->getLeft())
                    ->setParameter(sprintf("right_%d", $parentCategory->getId()), $parentCategory->getRight());
            }

            /** @phpstan-ignore-next-line */
            $queryBuilder->andWhere($queryBuilder->expr()->orX(...$expressions));
        }

        if ($filter->brand !== null) {
            $queryBuilder
                ->andWhere("b = :brand")
                ->setParameter("brand", $filter->brand);
        }

        if ($filter->keywords !== null) {
            $queryBuilder
                ->andWhere("CONCAT(p.name, ' ', p.description, ' ', b.name) LIKE :keywords")
                ->setParameter("keywords", "%" . $filter->keywords . "%");
        }
    }
}
