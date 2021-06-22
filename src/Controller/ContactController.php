<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User\User;
use App\Form\ContactType;
use App\Zendesk\Wrapper\ZendeskWrapperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contact", name="contact")
 */
class ContactController extends AbstractController
{
    public function __invoke(Request $request, ZendeskWrapperInterface $zendeskWrapper): Response
    {
        $contact = new Contact();

        if ($this->isGranted("ROLE_USER")) {
            /** @var User $user */
            $user = $this->getUser();
            $contact->name = $user->getFullName();
            $contact->email = $user->getEmail();
        }

        $form = $this->createForm(ContactType::class, $contact)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $zendeskWrapper->create($contact);
            $this->addFlash(
                "success",
                "Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais."
            );
            return $this->redirectToRoute("contact");
        }

        return $this->render("ui/contact.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
