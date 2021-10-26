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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $clientOrders = [];

        if ($this->isGranted('ROLE_SALES_PERSON') || $this->isGranted('ROLE_MANAGER')) {
            if ($request->query->get("field") === null) {
                $request->query->set("field", 'o.createdAt');
            }

            if ($request->query->get("direction") === null) {
                $request->query->set("direction", 'desc');
            }

            /** @var SalesPerson|Manager $employee */
            $employee = $user;

            $clientOrders = $orderRepository->getOrdersByMemberEmployee(
                $employee,
                $request->query->getInt("page", 1),
                10,
                $request->query->get("field"),
                $request->query->get("direction"),
                $request->query->get("filter")
            );
        }

        return $this->render("ui/order/index.html.twig", [
            "orders" => $orderRepository->findBy(["user" => $user], ["createdAt" => "desc"]),
            'clientOrders' => $clientOrders,
            "pages" => ceil(count($clientOrders) / 10),
        ]);
    }

    /**
     * @Route("/{id}/telecharger", name="order_download")
     */
    public function download(Order $order, string $publicDir): BinaryFileResponse
    {
        return $this->file(sprintf('%s/pdf/%s.pdf', $publicDir, $order->getReference()));
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
