<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User\Manager;
use App\Entity\User\User;
use App\Form\Account\EditPasswordType;
use App\Form\Account\EditPersonalInformationsType;
use App\Form\AddressType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/mon-compte")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/", name="account_index")
     */
    public function index(): Response
    {
        return $this->render('ui/account/index.html.twig');
    }

    /**
     * @Route("/modifier-mot-de-passe", name="account_edit_password")
     */
    public function editPassword(
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): Response {
        $form = $this->createForm(EditPasswordType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $user->setPassword($userPasswordEncoder->encodePassword($user, $form->get("plainPassword")->getData()));
            $user->setForgottenPasswordToken(null);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Votre mot de passe a été modifié avec succès.");

            return $this->redirectToRoute("account_index");
        }

        return $this->render('ui/account/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modifier-informations-personnelles", name="account_edit_personal_informations")
     */
    public function editPersonalInformations(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(EditPersonalInformationsType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Vos informations personnelles ont été modifiées avec succès.");

            return $this->redirectToRoute("account_index");
        }

        return $this->render('ui/account/edit_personal_informations.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
