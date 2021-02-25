<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\AccessFilterType;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/acces")
 */
class AccessController extends AbstractController
{
    /**
     * @param UserRepository<User> $userRepository
     * @Route("/", name="access_list")
     */
    public function list(UserRepository $userRepository, Request $request): Response
    {
        $form = $this->createForm(AccessFilterType::class)->handleRequest($request);

        $users = $userRepository->getPaginatedUsers(
            $request->query->getInt("page", 1),
            10,
            $form->get("keywords")->getData()
        );

        return $this->render("ui/access/list.html.twig", [
            "users" => $users,
            "pages" => ceil(count($users) / 10),
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/active", name="access_active")
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
            return $this->redirectToRoute("access_list");
        }

        return $this->render("ui/access/active.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/suspend", name="access_suspend")
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
            return $this->redirectToRoute("access_list");
        }

        return $this->render("ui/access/suspend.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * @Route("/{id}/delete", name="access_delete")
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
            return $this->redirectToRoute("access_list");
        }

        return $this->render("ui/access/delete.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }
}
