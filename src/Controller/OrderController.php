<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\Order;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use App\Repository\Order\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_SHOP")
 * @Route("/commandes")
 */
class OrderController extends AbstractController
{
    /**
     * @param OrderRepository<Order> $orderRepository
     * @Route("/", name="order_index")
     */
    public function index(OrderRepository $orderRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render("ui/order/index.html.twig", [
            "orders" => $orderRepository->findBy(["user" => $user], ["createdAt" => "desc"])
        ]);
    }

    /**
     * @param OrderRepository<Order> $orderRepository
     * @Route("/clients", name="order_clients")
     * @Security("is_granted('ROLE_SALES_PERSON') or is_granted('ROLE_MANAGER')")
     */
    public function clients(OrderRepository $orderRepository): Response
    {
        /** @var SalesPerson|Manager $user */
        $user = $this->getUser();

        return $this->render("ui/order/_clients.html.twig", [
            "orders" => $orderRepository->getOrdersByMemberEmployee($user)
        ]);
    }

    /**
     * @Route("/{id}/detail", name="order_detail")
     * @IsGranted("show", subject="order")
     */
    public function detail(Order $order): Response
    {
        return $this->render("ui/order/detail.html.twig", [
            "order" => $order
        ]);
    }
}
