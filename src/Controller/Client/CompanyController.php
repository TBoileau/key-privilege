<?php

declare(strict_types=1);

namespace App\Controller\Client;

use App\Entity\Company\Client;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Form\Client\Company\FilterType;
use App\Repository\Company\ClientRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/clients/societes")
 * @IsGranted("ROLE_CLIENT_COMPANY")
 */
class CompanyController extends AbstractController
{

    /**
     * @param ClientRepository<Client> $clientRepository
     * @Route("/", name="client_company_list")
     */
    public function list(ClientRepository $clientRepository, Request $request): Response
    {
        $form = $this->createForm(FilterType::class)->handleRequest($request);

        /** @var Manager|SalesPerson $employee */
        $employee = $this->getUser();

        $clients = $clientRepository->getPaginatedClients(
            $employee,
            $request->query->getInt("page", 1),
            10,
            $form->get("keywords")->getData()
        );

        return $this->render("ui/client/company/list.html.twig", [
            "clients" => $clients,
            "pages" => ceil(count($clients) / 10),
            "form" => $form->createView()
        ]);
    }
}
