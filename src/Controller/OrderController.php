<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\Order;
use App\Entity\Order\Sav;
use App\Entity\User\User;
use App\Form\SavType;
use App\Repository\Order\OrderRepository;
use App\Zendesk\Wrapper\ZendeskWrapperInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(OrderRepository $orderRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render("ui/order/index.html.twig", [
            "orders" => $orderRepository->findBy(["user" => $user], ["createdAt" => "desc"])
        ]);
    }

    /**
     * @Route("/{id}/detail", name="order_detail")
     */
    public function detail(Order $order): Response
    {
        return $this->render("ui/order/detail.html.twig", [
            "order" => $order
        ]);
    }

    /**
     * @Route("/{id}/declencher-sav", name="order_trigger_sav")
     */
    public function triggerSAV(Order $order, Request $request): Response
    {
        $sav = new Sav();

        $form = $this->createForm(SavType::class, $sav, ["order" => $order])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                "success",
                "Votre demande de SAV a bien été envoyée. Nous vous répondrons dans les plus brefs délais."
            );
            return $this->redirectToRoute("order_index");
        }

        return $this->render("ui/order/trigger_sav.html.twig", [
            "form" => $form->createView(),
            "order" => $order
        ]);
    }
}
