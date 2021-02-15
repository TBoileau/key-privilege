<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\AccessFilterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/access")
 */
class AccessController extends AbstractController
{
    /**
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

        return $this->render("access/list.html.twig", [
            "users" => $users,
            "pages" => ceil(count($users) / 10),
            "form" => $form->createView()
        ]);
    }
}
