<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Shop\Product;
use App\Entity\User\User;
use App\Repository\Shop\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="home")
 */
class HomeController extends AbstractController
{
    /**
     * @param ProductRepository<Product> $productRepository
     */
    public function __invoke(ProductRepository $productRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render("ui/home.html.twig", [
            "products" => $productRepository->getLastProducts($user)
        ]);
    }
}
