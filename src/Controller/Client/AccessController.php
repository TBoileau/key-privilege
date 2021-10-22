<?php

declare(strict_types=1);

namespace App\Controller\Client;

use App\Entity\Company\Client;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Form\Client\Access\FilterType;
use App\Form\Client\Access\AccessType;
use App\Repository\User\CustomerRepository;
use DateTime;
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
 * @Route("/clients/acces")
 * @IsGranted("ROLE_CLIENT_ACCESS")
 */
class AccessController extends AbstractController
{
    /**
     * @Route("/{id}/modifier", name="client_access_update")
     * @IsGranted("update", subject="customer")
     */
    public function update(Customer $customer, Request $request): Response
    {
        /** @var SalesPerson|Manager $employee */
        $employee = $this->getUser();

        $form = $this->createForm(AccessType::class, $customer, ["employee" => $employee])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($customer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été modifié avec succès.",
                    $customer->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/client/access/update.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/creer/{id}", name="client_access_create", defaults={"id"=null})
     * @IsGranted("ROLE_CLIENT_ACCESS_CREATE")
     */
    public function create(
        ?Client $client,
        Request $request,
        MailerInterface $mailer,
        UserPasswordEncoderInterface $customerPasswordEncoder
    ): Response {
        $customer = new Customer();

        if ($client !== null) {
            $customer->setClient($client);
        }

        /** @var SalesPerson|Manager $employee */
        $employee = $this->getUser();

        $customer->setDeliveryAddress(new \App\Entity\Address());
        $customer->getDeliveryAddress()
            ->setProfessional($employee->getDeliveryAddress()->isProfessional())
            ->setCompanyName($employee->getDeliveryAddress()->getCompanyName())
            ->setFirstName($employee->getDeliveryAddress()->getFirstName())
            ->setLastName($employee->getDeliveryAddress()->getLastName())
            ->setStreetAddress($employee->getDeliveryAddress()->getStreetAddress())
            ->setRestAddress($employee->getDeliveryAddress()->getRestAddress())
            ->setZipCode($employee->getDeliveryAddress()->getZipCode())
            ->setEmail($employee->getDeliveryAddress()->getEmail())
            ->setPhone($employee->getDeliveryAddress()->getPhone());

        $form = $this->createForm(AccessType::class, $customer, ["employee" => $employee])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = md5(random_bytes(8));
            $customer->setPassword($customerPasswordEncoder->encodePassword($customer, $password));
            $this->getDoctrine()->getManager()->persist($customer);
            $this->getDoctrine()->getManager()->flush();
            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->to(new Address($customer->getEmail(), $customer->getFullName()))
                    ->htmlTemplate("emails/welcome.html.twig")
                    ->context(["username" => $customer->getUsername(), "password" => $password])
            );
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été créé avec succès.",
                    $customer->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/client/access/create.html.twig", [
            "form" => $form->createView(),
            "deliveryAddresses" => $employee->getDeliveryAddresses()
        ]);
    }

    /**
     * @param CustomerRepository<Customer> $customerRepository
     * @Route("/", name="client_access_list")
     */
    public function list(CustomerRepository $customerRepository, Request $request): Response
    {
        $form = $this->createForm(FilterType::class)->handleRequest($request);

        /** @var Manager|SalesPerson $employee */
        $employee = $this->getUser();

        $customers = $customerRepository->getPaginatedCustomers(
            $employee,
            $request->query->getInt("page", 1),
            10,
            $form->get("keywords")->getData()
        );

        return $this->render("ui/client/access/list.html.twig", [
            "customers" => $customers,
            "pages" => ceil(count($customers) / 10),
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/active", name="client_access_active")
     * @IsGranted("active", subject="customer")
     */
    public function active(Customer $customer, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setSuspended(false);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été réactivé avec succès.",
                    $customer->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/client/access/active.html.twig", [
            "form" => $form->createView(),
            "customer" => $customer
        ]);
    }

    /**
     * @Route("/{id}/reset", name="client_access_reset")
     * @IsGranted("reset", subject="customer")
     */
    public function reset(
        Customer $customer,
        Request $request,
        UserPasswordEncoderInterface $customerPasswordEncoder,
        MailerInterface $mailer
    ): Response {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = md5(random_bytes(8));
            $customer->setPassword($customerPasswordEncoder->encodePassword($customer, $password));
            $this->getDoctrine()->getManager()->flush();
            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->to(new Address($customer->getEmail(), $customer->getFullName()))
                    ->htmlTemplate("emails/reset.html.twig")
                    ->context(["customer" => $customer, "password" => $password])
            );
            $this->addFlash(
                "success",
                sprintf(
                    "Un nouveau mot de passe a été généré et envoyé à %s.",
                    $customer->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/client/access/reset.html.twig", [
            "form" => $form->createView(),
            "customer" => $customer
        ]);
    }

    /**
     * @Route("/{id}/suspend", name="client_access_suspend")
     * @IsGranted("suspend", subject="customer")
     */
    public function suspend(Customer $customer, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setSuspended(true);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été suspendu avec succès.",
                    $customer->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/client/access/suspend.html.twig", [
            "form" => $form->createView(),
            "customer" => $customer
        ]);
    }

    /**
     * @Route("/{id}/delete", name="client_access_delete")
     * @IsGranted("delete", subject="customer")
     */
    public function delete(Customer $customer, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->remove($customer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                sprintf(
                    "L'accès de %s a été supprimé avec succès.",
                    $customer->getFullName()
                )
            );
            return $this->redirectToRoute("client_access_list");
        }

        return $this->render("ui/client/access/delete.html.twig", [
            "form" => $form->createView(),
            "customer" => $customer
        ]);
    }
}
