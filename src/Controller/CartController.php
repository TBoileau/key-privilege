<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\Line;
use App\Entity\Order\Order;
use App\Entity\Shop\Product;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Employee;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use App\Form\Order\OrderType;
use App\Repository\Order\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

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
     * @Route("/{id}/supprimer", name="cart_delete")
     */
    public function delete(Line $line): RedirectResponse
    {
        $this->getDoctrine()->getManager()->remove($line);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("cart_index");
    }

    /**
     * @param OrderRepository<Order> $orderRepository
     * @Route("/", name="cart_index")
     */
    public function index(
        OrderRepository $orderRepository,
        Request $request,
        WorkflowInterface $orderStateMachine
    ): Response {
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

        $form = null;

        if ($user->getDeliveryAddress() !== null) {
            $order->setDeliveryAddress($user->getDeliveryAddress());

            $form = $this->createForm(OrderType::class, $order)->handleRequest($request);

            if (
                $orderStateMachine->can($order, "valid_cart")
                && $form->isSubmitted()
                && $form->isValid()
            ) {
                if ($user instanceof Customer) {
                    $order->setBillingAddress($user->getClient()->getMember()->getBillingAddress());
                } else {
                    /** @var Manager|Collaborator|SalesPerson $user */
                    $order->setBillingAddress($user->getMember()->getBillingAddress());
                }

                $order->setDeliveryAddress($user->getDeliveryAddress());
                $orderStateMachine->apply($order, "valid_cart");
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash("success", "Votre commande a été enregistrée avec succès.");
                return $this->redirectToRoute("order_index");
            }
        }

        return $this->render("ui/cart/index.html.twig", [
            "order" => $order,
            "form" => $form?->createView()
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

        return $this->redirectToRoute("shop_product", ["slug" => $product->getSlug(), "cart" => true]);
    }
}
