<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Entity\Rules;
use App\Form\Security\ForgottenPasswordType;
use App\Form\Security\ResetPasswordType;
use App\Form\Security\RulesType;
use App\Repository\RulesRepository;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;

class SecurityController extends AbstractController
{
    /**
     * @Route("/reinitialisation-mot-de-passe/{forgottenPasswordToken}", name="security_reset_password")
     */
    public function resetPassword(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): Response {
        $form = $this->createForm(ResetPasswordType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordEncoder->encodePassword($user, $form->get("plainPassword")->getData()));
            $user->setForgottenPasswordToken(null);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Votre mot de passe a été modifié avec succès.");

            return $this->redirectToRoute("security_login");
        }

        return $this->render('ui/security/reset_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/mot-de-passe-oublie", name="security_forgotten_password")
     * @param UserRepository<User> $userRepository
     */
    public function forgottenPassword(
        Request $request,
        MailerInterface $mailer,
        UserRepository $userRepository
    ): Response {
        $form = $this->createForm(ForgottenPasswordType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(["username" => $form->get("username")->getData()]);

            if ($user !== null) {
                $user->setForgottenPasswordToken((string) Uuid::v4());
                $this->getDoctrine()->getManager()->flush();
                $mailer->send(
                    (new TemplatedEmail())
                        ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                        ->to(new Address($user->getEmail(), $user->getFullName()))
                        ->htmlTemplate("emails/forgotten_password.html.twig")
                        ->context(["user" => $user])
                );
                $this->addFlash(
                    "success",
                    sprintf(
                        "Un email a été envoyé à %s avec la procédure à suivre pour réinitialiser votre mot de passe",
                        $user->getEmail()
                    )
                );
            }

            return $this->redirectToRoute("security_login");
        }

        return $this->render('ui/security/forgotten_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reglement", name="security_rules")
     * @param RulesRepository<Rules> $rulesRepository
     */
    public function rules(Request $request, RulesRepository $rulesRepository): Response
    {
        $rules = $rulesRepository->getLastPublishedRules();

        $form = $this->createForm(RulesType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            /** @var SubmitButton $acceptButton */
            $acceptButton = $form->get("accept");

            if ($acceptButton->isClicked()) {
                $user->acceptRules($rules);
            } else {
                $user->refuseRules($rules);
            }
            $this->getDoctrine()->getManager()->flush();

            if (!$user->hasAcceptedRules($rules)) {
                $this->addFlash("warning", "Vous avez refusé le nouveau règlement.");
                return $this->redirectToRoute("security_logout");
            }

            $this->addFlash("success", "Bienvenue sur Key Privilege.");
            return $this->redirectToRoute("home");
        }
        return $this->render('ui/security/rules.html.twig', [
            "rules" => $rules,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('ui/security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     * @codeCoverageIgnore
     */
    public function logout(): void
    {
    }
}
