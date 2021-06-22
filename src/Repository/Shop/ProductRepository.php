<?php

namespace App\Repository\Shop;

use App\Entity\Shop\Category;
use App\Entity\Shop\Filter;
use App\Entity\Shop\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template T
 * @extends ServiceEntityRepository<T>
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
    public function getLastProducts(): array
    {
        return $this->createQueryBuilder("p")
            ->addSelect("b")
            ->addSelect("c")
            ->join("p.brand", "b")
            ->join("p.category", "c")
            ->leftJoin("c.lastProduct", "lp")
            ->setMaxResults(4)
            ->orderBy("lp.id", "desc")
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
        Filter $filter
    ): Paginator {
        $queryBuilder = $this->createQueryBuilder("p")
            ->addSelect("b")
            ->addSelect("c")
            ->join("p.brand", "b")
            ->join("p.category", "c")
            ->leftJoin("c.lastProduct", "lp")
            ->andWhere("p.amount >= :min")
            ->setParameter("min", $filter->min)
            ->andWhere("p.amount <= :max")
            ->setParameter("max", $filter->max)
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);

        if ($category !== null) {
            $queryBuilder
                ->andWhere("c.left >= :left")
                ->andWhere("c.right <= :right")
                ->setParameter("left", $category->getLeft())
                ->setParameter("right", $category->getRight());
        }

        if ($filter->brand !== null) {
            $queryBuilder
                ->andWhere("b = :brand")
                ->setParameter("brand", $filter->brand);
        }

        if ($filter->keywords !== null) {
            $queryBuilder
                ->andWhere("CONCAT(p.name, ' ', p.description, ' ', b.name) LIKE :keywords")
                ->setParameter("keywords", $filter->keywords);
        }

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
}
