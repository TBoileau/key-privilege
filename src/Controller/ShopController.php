<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\Order;
use App\Entity\Shop\Category;
use App\Entity\Shop\Filter;
use App\Entity\Shop\Product;
use App\Entity\Shop\Universe;
use App\Entity\User\User;
use App\Form\Shop\FilterType;
use App\Repository\Order\OrderRepository;
use App\Repository\Shop\ProductRepository;
use App\Repository\Shop\UniverseRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_SHOP")
 * @Route("/boutique")
 */
class ShopController extends AbstractController
{
    /**
     * @Route("/products/{slug}/{cart}", name="shop_product", defaults={"cart"=false})
     */
    public function product(Product $product, bool $cart): Response
    {
        return $this->render("ui/shop/product.html.twig", ["product" => $product, "cart" => $cart]);
    }

    /**
     * @param UniverseRepository<Universe> $universeRepository
     * @param ProductRepository<Product> $productRepository
     * @Route("/{universe}/{category}", name="shop_index", defaults={"category"=null, "universe"=null})
     * @Entity("universe", expr="repository.findOneBySlug(universe)")
     * @Entity("category", expr="repository.findOneBySlug(category)")
     */
    public function index(
        ?Universe $universe,
        ?Category $category,
        UniverseRepository $universeRepository,
        ProductRepository $productRepository,
        Request $request
    ): Response {
        $min = $productRepository->getMinAmount();
        $max = $productRepository->getMaxAmount();

        $filter = new Filter();
        $filter->min = $min;
        $filter->max = $max;

        $form = $this->createForm(FilterType::class, $filter)->handleRequest($request);

        $products = $productRepository->getPaginatedProducts(
            $request->query->getInt("page", 1),
            $request->query->getInt("limit", 18),
            $request->query->get("sort", "new-products"),
            $category,
            $universe,
            $filter
        );

        $pages = ceil(count($products) / $request->query->getInt("limit", 18));

        return $this->render("ui/shop/index.html.twig", [
            "universes" => $universeRepository->getUniverses(),
            "products" => $products,
            "category" => $category,
            "universe" => $universe,
            "params" => array_merge(
                $request->query->all(),
                [
                    "category" => $category?->getSlug(),
                    "universe" => $universe?->getSlug(),
                    "page" =>  $request->query->getInt("page", 1),
                    "limit" => $request->query->getInt("limit", 18),
                    "sort" => $request->query->get("sort", "new-products")
                ]
            ),
            "form" => $form->createView(),
            "min" => $min,
            "max" => $max,
            "pages" => $pages,
            "pageRange" => range(
                max(1, $request->query->getInt("page", 1) - 3),
                min($pages, $request->query->getInt("page", 1) + 3)
            )
        ]);
    }
}
