<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Key\Purchase;
use App\Entity\User\Manager;
use App\Form\Key\PurchaseType;
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
     * @Route("/acheter", name="key_purchase")
     * @IsGranted("ROLE_KEY_BUY")
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
            return $this->redirectToRoute("key_purchase");
        }

        return $this->render("ui/key/purchase.html.twig", ["form" => $form->createView()]);
    }
}
