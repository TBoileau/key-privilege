<?php

declare(strict_types=1);

namespace App\Controller\Client;

use App\Entity\Company\Client;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Form\Client\Access\AccessType;
use App\Form\Client\Company\CompanyType;
use App\Form\Client\Company\FilterType;
use App\Repository\Company\ClientRepository;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

        /** @var Manager $employee */
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

    /**
     * @Route("/{id}/modifier", name="client_company_update")
     * @IsGranted("update", subject="client")
     */
    public function update(Client $client, Request $request): Response
    {
        /** @var Manager $employee */
        $employee = $this->getUser();
        $client->setMember($employee->getMember());

        $form = $this->createForm(CompanyType::class, $client, ["employee" => $employee])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "Le client %s a été modifié avec succès.",
                    $client->getName()
                )
            );
            return $this->redirectToRoute("client_company_list");
        }

        return $this->render("ui/client/company/update.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/creer", name="client_company_create")
     */
    public function create(Request $request): Response
    {
        $client = new Client();

        /** @var Manager $employee */
        $employee = $this->getUser();
        $client->setMember($employee->getMember());

        $form = $this->createForm(CompanyType::class, $client, ["employee" => $employee])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->getAddress()->setProfessional(true);
            $client->getAddress()->setCompanyName($client->getName());
            $this->getDoctrine()->getManager()->persist($client);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "Le client %s a été créé avec succès.",
                    $client->getName()
                )
            );
            return $this->redirectToRoute("client_access_create", [
                "id" => $client->getId()
            ]);
        }

        return $this->render("ui/client/company/create.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/{id}/delete", name="client_company_delete")
     * @IsGranted("delete", subject="client")
     */
    public function delete(Client $client, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->remove($client);
            foreach ($client->getCustomers() as $customer) {
                $this->getDoctrine()->getManager()->remove($customer);
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "Le client %s a été supprimé avec succès, ainsi que tous les accès associés.",
                    $client->getName()
                )
            );
            return $this->redirectToRoute("client_company_list");
        }

        return $this->render("ui/client/company/delete.html.twig", [
            "form" => $form->createView(),
            "client" => $client
        ]);
    }
}
