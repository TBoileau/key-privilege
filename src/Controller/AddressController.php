<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Employee;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use App\Form\AddressType;
use App\Form\NewAddressType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/mon-compte/mes-adresses")
 * @IsGranted("ROLE_ADDRESS")
 */
class AddressController extends AbstractController
{
    /**
     * @Route("/", name="address_list")
     */
    public function list(): Response
    {
        return $this->render('ui/address/list.html.twig');
    }

    /**
     * @Route("/creer", name="address_create")
     */
    public function create(Request $request): Response
    {
        $address = new Address();

        /** @var SalesPerson|Collaborator|Manager|Customer $user */
        $user = $this->getUser();
        $address->setEmail($user->getDeliveryAddress()->getEmail());
        $address->setPhone($user->getDeliveryAddress()->getPhone());

        if (!$user instanceof Manager) {
            $request->query->set('type', 'delivery');
        }

        $form = $this->createForm(
            NewAddressType::class,
            $address,
            ['type' => $request->query->get('type', null)]
        )->handleRequest($request);

        $types = [
            "billing" => [
                'default' => static fn (Manager $manager) => $manager
                    ->getMember()
                    ->setBillingAddress($address),
                'collection' => static fn (Manager $manager) => $manager
                    ->getMember()
                    ->getBillingAddresses()
                    ->add($address),
            ],
            "delivery" => [
                'default' => static fn (User $user) => $user->setDeliveryAddress($address),
                'collection' => static fn (User $user) => $user->getDeliveryAddresses()->add($address),
            ]
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Manager $manager */
            $manager = $user;

            if ($form->has('type')) {
                /** @var string $type */
                $type = $form->get("type")->getData();
                /** @var boolean $default */
                $default = $form->get("default")->getData();
                $types[$type]["collection"]($manager);
                if ($default) {
                    $types[$type]["default"]($manager);
                }
            } else {
                $type = $request->query->get('type', "delivery");
                $default = $form->get("default")->getData();

                $types[$type]["collection"]($manager);
                if ($default) {
                    $types[$type]["default"]($manager);
                }
            }

            $this->getDoctrine()->getManager()->persist($address);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "L'adresse a été ajoutée avec succès.");

            return $this->redirectToRoute("address_list");
        }

        return $this->render('ui/address/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="address_update")
     */
    public function update(Address $originalAddress, Request $request): Response
    {
        $address = clone $originalAddress;

        /** @var SalesPerson|Collaborator|Manager|Customer $user */
        $user = $this->getUser();

        $type = "delivery";

        if ($user->getDeliveryAddress() === $originalAddress) {
            $user->setDeliveryAddress($address);
        }

        if ($user instanceof Manager) {
            if ($user->getMember()->getBillingAddress() === $originalAddress) {
                $user->getMember()->setBillingAddress($address);
            }

            if ($user->getMember()->getBillingAddresses()->contains($originalAddress)) {
                $type = "billing";
            }
        }


        $form = $this->createForm(AddressType::class, $address)->handleRequest($request);

        $types = [
            "billing" => [
                'collection' => static fn (Manager $manager) => $manager
                    ->getMember()
                    ->getBillingAddresses()
                    ->add($address),
            ],
            "delivery" => [
                'collection' => static fn (User $user) => $user->getDeliveryAddresses()->add($address),
            ]
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $originalAddress->setDeleted(true);

            /** @var Manager $manager */
            $manager = $user;

            $types[$type]["collection"]($manager);

            $this->getDoctrine()->getManager()->persist($address);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "L'adresse a été modifiée avec succès.");

            return $this->redirectToRoute("address_list");
        }

        return $this->render('ui/address/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/defaut", name="address_default")
     */
    public function default(Address $address): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user instanceof Manager) {
            if ($user->getMember()->getBillingAddresses()->contains($address)) {
                $user->getMember()->setBillingAddress($address);
            }
        }

        if ($user->getDeliveryAddresses()->contains($address)) {
            $user->setDeliveryAddress($address);
        }
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'L\'adresse par défaut a été modifiée avec succès.');
        return $this->redirectToRoute('address_list');
    }

    /**
     * @Route("/{id}/supprimer", name="address_delete")
     */
    public function delete(Address $address, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm()->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setDeleted(true);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", 'L\'adresse a été supprimée avec succès.');
            return $this->redirectToRoute("address_list");
        }

        return $this->render("ui/address/delete.html.twig", [
            "form" => $form->createView(),
            "address" => $address
        ]);
    }
}
