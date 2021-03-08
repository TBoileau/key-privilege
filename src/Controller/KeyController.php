<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use App\Entity\Key\Transfer;
use App\Entity\User\Manager;
use App\Form\Key\PurchaseType;
use App\Form\Key\TransferType;
use App\UseCase\TransferPointsInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cles")
 */
class KeyController extends AbstractController
{
    /**
     * @Route("/", name="key_index")
     */
    public function index(): Response
    {
        return $this->render("ui/key/index.html.twig");
    }

    /**
     * @Route("/historique/{id}", name="key_history")
     */
    public function history(Account $account): Response
    {
        return $this->render("ui/key/history.html.twig", [
            "account" => $account
        ]);
    }

    /**
     * @Route("/transferer", name="key_transfer")
     * @IsGranted("ROLE_KEY_TRANSFER")
     */
    public function transfer(Request $request, TransferPointsInterface $transferPoints): Response
    {
        /** @var Manager $manager */
        $manager = $this->getUser();

        $transfer = new Transfer();

        $form = $this->createForm(TransferType::class, $transfer, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transferPoints->execute($transfer);
            $this->getDoctrine()->getManager()->persist($transfer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                "Le transfert de clés a été effectué avec succès."
            );
            return $this->redirectToRoute("key_index");
        }

        return $this->render("ui/key/transfer.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/acheter", name="key_purchase")
     * @IsGranted("ROLE_KEY_PURCHASE")
     */
    public function purchase(Request $request, MailerInterface $mailer): Response
    {
        /** @var Manager $manager */
        $manager = $this->getUser();

        $purchase = new Purchase();

        if ($manager->getMembers()->count() === 1) {
            $purchase->setAccount($manager->getMember()->getAccount());
        }

        $form = $this->createForm(PurchaseType::class, $purchase, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchase->prepare();
            $this->getDoctrine()->getManager()->persist($purchase);
            $this->getDoctrine()->getManager()->flush();
            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->to(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->htmlTemplate("emails/key_purchase.html.twig")
                    ->context(["purchase" => $purchase])
            );
            $this->addFlash(
                "success",
                "
                    Votre demande d'achat de clés a été envoyée avec succès. 
                    Dès réception du paiement, les clés vous seront crédités.
                "
            );
            return $this->redirectToRoute("key_index");
        }

        return $this->render("ui/key/purchase.html.twig", ["form" => $form->createView()]);
    }
}
