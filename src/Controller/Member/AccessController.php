<?php

declare(strict_types=1);

namespace App\Controller\Member;

use App\Entity\User\Collaborator;
use App\Entity\User\User;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Form\Member\Access\FilterType;
use App\Form\Member\Access\AccessType;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/adherents/acces")
 * @IsGranted("ROLE_MANAGER")
 */
class AccessController extends AbstractController
{
    /**
     * @Route("/{id}/modifier", name="member_access_update")
     * @IsGranted("update", subject="user")
     */
    public function update(User $user, Request $request): Response
    {
        /** @var Manager $manager */
        $manager = $this->getUser();
        $form = $this->createForm(AccessType::class, $user, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été modifié avec succès.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("member_access_list");
        }

        return $this->render("ui/member/access/update.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/creer/{role}", name="member_access_create")
     */
    public function create(
        string $role,
        Request $request,
        MailerInterface $mailer,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): Response {
        /** @var null|SalesPerson|Collaborator|Manager $user */
        $user = null;
        switch ($role) {
            case "collaborateur":
                $user = new Collaborator();
                break;
            case "administrateur":
                $user = new Manager();
                break;
            case "commercial":
                $user = new SalesPerson();
                break;
        }

        /** @var Manager $manager */
        $manager = $this->getUser();

        if ($manager->getMembers()->count() === 1) {
            $user->setMember($manager->getMember());
        }

        $form = $this->createForm(AccessType::class, $user, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = md5(random_bytes(8));
            $user->setPassword($userPasswordEncoder->encodePassword($user, $password));
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->to(new Address($user->getEmail(), $user->getFullName()))
                    ->htmlTemplate("emails/welcome.html.twig")
                    ->context(["username" => $user->getUsername(), "password" => $password])
            );
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été créé avec succès.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("member_access_list");
        }

        return $this->render("ui/member/access/create.html.twig", ["form" => $form->createView(), "role" => $role]);
    }

    /**
     * @param UserRepository<SalesPerson|Collaborator|Manager> $userRepository
     * @Route("/", name="member_access_list")
     */
    public function list(UserRepository $userRepository, Request $request): Response
    {
        $form = $this->createForm(FilterType::class)->handleRequest($request);

        /** @var Manager $manager */
        $manager = $this->getUser();

        $employees = $userRepository->getPaginatedEmployees(
            $manager,
            $request->query->getInt("page", 1),
            10,
            $form->get("keywords")->getData()
        );

        return $this->render("ui/member/access/list.html.twig", [
            "employees" => $employees,
            "pages" => ceil(count($employees) / 10),
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/active", name="member_access_active")
     * @IsGranted("active", subject="user")
     */
    public function active(User $user, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setSuspended(false);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été réactivé avec succès.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("member_access_list");
        }

        return $this->render("ui/member/access/active.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/reset", name="member_access_reset")
     * @IsGranted("reset", subject="user")
     */
    public function reset(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder,
        MailerInterface $mailer
    ): Response {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = md5(random_bytes(8));
            $user->setPassword($userPasswordEncoder->encodePassword($user, $password));
            $this->getDoctrine()->getManager()->flush();
            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->to(new Address($user->getEmail(), $user->getFullName()))
                    ->htmlTemplate("emails/reset.html.twig")
                    ->context(["user" => $user, "password" => $password])
            );
            $this->addFlash(
                "success",
                sprintf(
                    "Un nouveau mot de passe a été généré et envoyé à %s.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("member_access_list");
        }

        return $this->render("ui/member/access/reset.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/suspend", name="member_access_suspend")
     * @IsGranted("suspend", subject="user")
     */
    public function suspend(User $user, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setSuspended(true);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été suspendu avec succès.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("member_access_list");
        }

        return $this->render("ui/member/access/suspend.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/delete", name="member_access_delete")
     * @IsGranted("delete", subject="user")
     */
    public function delete(User $user, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->remove($user);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été supprimé avec succès.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("member_access_list");
        }

        return $this->render("ui/member/access/delete.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }
}
