<?php

declare(strict_types=1);

namespace App\Controller\Client;

use App\Entity\User\User;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Form\AccessFilterType;
use App\Repository\CustomerRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/clients/acces")
 * @IsGranted("ROLE_CLIENT_ACCESS")
 */
class AccessController extends AbstractController
{
    /**
     * @param CustomerRepository<User> $userRepository
     * @Route("/", name="client_access_list")
     */
    public function clients(CustomerRepository $userRepository, Request $request): Response
    {
        $form = $this->createForm(AccessFilterType::class)->handleRequest($request);

        /** @var Manager|SalesPerson $user */
        $user = $this->getUser();

        $users = $userRepository->getPaginatedUsers(
            $user,
            $request->query->getInt("page", 1),
            10,
            $form->get("keywords")->getData()
        );

        return $this->render("ui/access/clients.html.twig", [
            "users" => $users,
            "pages" => ceil(count($users) / 10),
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/active", name="client_access_active")
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
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/access/active.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/reset", name="client_access_reset")
     */
    public function reset(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): Response {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordEncoder->encodePassword($user, md5(random_bytes(8))));
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "Un nouveau mot de passe a été généré et envoyé à de %s.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/access/reset.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/suspend", name="client_access_suspend")
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
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/access/suspend.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/delete", name="client_access_delete")
     */
    public function delete(User $user, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setDeletedAt(new DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été supprimé avec succès.",
                    $user->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/access/delete.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }
}
