<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\Line;
use App\Entity\Order\Order;
use App\Entity\Shop\Product;
use App\Entity\User\User;
use App\Repository\Order\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_SHOP")
 * @Route("/panier")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/{id}/augmenter-quantite", name="cart_increase_quantity")
     */
    public function increaseQuantity(Line $line): RedirectResponse
    {
        $line->increaseQuantity();
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("cart_index");
    }

    /**
     * @Route("/{id}/diminuer-quantite", name="cart_decrease_quantity")
     */
    public function decreaseQuantity(Line $line): RedirectResponse
    {
        $line->decreaseQuantity();

        if ($line->getQuantity() === 0) {
            $this->getDoctrine()->getManager()->remove($line);
        }

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("cart_index");
    }

    /**
     * @param OrderRepository<Order> $orderRepository
     * @Route("/", name="cart_index")
     */
    public function index(OrderRepository $orderRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var ?Order $order */
        $order = $orderRepository->findOneBy([
            "state" => "cart",
            "user" => $user
        ]);

        if ($order === null) {
            $order = (new Order())->setUser($user);
        }

        return $this->render("ui/cart/index.html.twig", [
            "order" => $order
        ]);
    }

    /**
     * @param OrderRepository<Order> $orderRepository
     * @Route("/ajouter/{slug}", name="cart_add")
     */
    public function add(Product $product, OrderRepository $orderRepository): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var ?Order $order */
        $order = $orderRepository->findOneBy([
            "state" => "cart",
            "user" => $user
        ]);

        if ($order === null) {
            $order = (new Order())->setUser($user);
            $this->getDoctrine()->getManager()->persist($order);
        }

        $order->addProduct($product);

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash("success", "Produit ajouté au panier avec succès.");
        return $this->redirectToRoute("shop_product", ["slug" => $product->getSlug()]);
    }
}
