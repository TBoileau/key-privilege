<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgottenPasswordType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        return $this->render('security/reset_password.html.twig', [
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
            $user = $userRepository->findOneBy(["email" => $form->get("email")->getData()]);

            if ($user !== null) {
                $user->setForgottenPasswordToken((string) Uuid::v4());
                $this->getDoctrine()->getManager()->flush();
                $mailer->send(
                    (new TemplatedEmail())
                        ->from(new Address("contact@key-privilege.fr", "Key Privilege"))
                        ->to(new Address($user->getEmail(), $user->getFullName()))
                        ->htmlTemplate("emails/forgotten_password.html.twig")
                        ->context(["user" => $user])
                );
            }

            $this->addFlash("success", <<<EOF
Un email a été envoyé avec la procédure à suivre pour réinitialiser votre mot de passe
EOF
            );

            return $this->redirectToRoute("security_login");
        }

        return $this->render('security/forgotten_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
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
