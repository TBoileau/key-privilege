<?php

use App\Entity\Address;
use App\Entity\Order\Line;
use App\Entity\Order\Order;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Employee;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Repository\Order\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

ini_set("display_errors",1);
ini_set("memory_limit","512M");
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Europe/Paris');

/** @var EntityManagerInterface $entityManager */
$entityManager = require __DIR__ . "/../build/doctrine.php";

$request = Request::createFromGlobals();

if($request->isMethod(Request::METHOD_GET) && $request->get("ACTION") === "getCommandes"){
    $action = $_GET["ACTION"] ?? $_POST["ACTION"];

    /** @var OrderRepository $orderRepository */
    $orderRepository = $entityManager->getRepository(Order::class);

    /** @var array<array-key, Order> $orders */
    $orders = $orderRepository->findBy(['state' => 'pending']);

    /** @var array<int, array<string, array<string, mixed>>> $response */
    $response = [];

    foreach($orders as $k => $order) {
        $response[$k]["LIGNES"] = [];
        $response[$k]["USERS"] = [];

        /** @var Line $line */
        foreach ($order->getLines() as $line) {
            $response[$k]["LIGNES"][] = [
                "IDBDC_ELT" => $line->getId(),
                "IDPRODUIT" => $line->getProduct()->getId(),
                "PAUHT" => round($line->getPurchasePrice() / 100),
                "PVUHT" => round($line->getSalePrice() / 100),
                "VALEUR" => $line->getAmount(),
                "PPGC" => round($line->getPurchasePrice() / 100),
                "IDBDC" => $order->getId(),
                "QTE" => $line->getQuantity(),
                "REFERENCE" => $line->getProduct()->getReference(),
                "DESIGN" => $line->getProduct()->getDescription(),
                "MONTANT" => round(($line->getSalePrice() * $line->getQuantity()) / 100),
                "IDTVA" => $line->getVat(),
            ];
        }

        $user = $order->getUser();

        /** @var array<array-key, string> $emailsInCopy */
        $emailsInCopy = [];

        if ($user instanceof Customer) {
            $company = $user->getClient();
            $emailsInCopy[] = $company->getSalesPerson()->getEmail();

            $emailsInCopy = array_merge(
                $emailsInCopy,
                $company->getMember()
                    ->getManagers()
                    ->filter(static fn (Manager $manager) => $manager->isInEmailCopy())
                    ->map(static fn (Manager $manager) => $manager->getEmail())
                    ->toArray()
            );
        } else {
            /** @var Employee $user */
            $company = $user->getMember();
        }


        $addDetail = static fn (Address $address, int $property, mixed $value) => [
            "IDCONTACT" => $address->getId(),
            "IDPROPRIETE" => $property,
            "VALEUR" => $value,
            "IDVALEUR" => 0
        ];

        $addAddress = static fn (Address $address, string $type): array => [
            "IDCONTACT" => $address->getId(),
            "IDUSER" => $order->getUser()->getId(),
            "IDTYPE" =>  match ($type) {
                "delivery" => 3,
                "billing" => 2,
                "other" => 1
            },
            "VISIBLE" => 1,
            "DETAILS" => [
                $addDetail($address, 2, $address->getName()),
                $addDetail($address, 3, $address->getFullName()),
                $addDetail($address, 4, $address->getStreetAddress()),
                $addDetail($address, 5, $address->getRestAddress() ?? ""),
                $addDetail($address, 6, ""),
                $addDetail($address, 7, $address->getZipCode()),
                $addDetail($address, 8, $address->getLocality()),
                $addDetail($address, 9, $address->getPhone()),
                $addDetail($address, 10, $address->getPhone()),
                $addDetail($address, 11, ""),
                $addDetail($address, 12, 1),
                $addDetail($address, 13, 1),
                $addDetail($address, 14, 1),
                $addDetail($address, 15, $address->getEmail()),
                $addDetail($address, 16, ""),
                $addDetail($address, 17, implode(";", array_unique($emailsInCopy))),
                $addDetail($address, 18, ""),
                $addDetail($address, 19, ""),
            ]
        ];

        $response[$k]["USERS"][0] = [
            "IDUSER" => $order->getUser()->getId(),
            "NOM" => $order->getUser()->getLastName(),
            "PRENOM" => $order->getUser()->getLastName(),
            "IDSOC" => $company->getId(),
            "IDPROFIL" => match($order->getUser()::class) {
                Manager::class => 2,
                SalesPerson::class => 3,
                Collaborator::class => 5,
                Customer::class => 4,
            },
            "ACTIF" => 1,
            "EMAIL" => $order->getUser()->getEmail(),
            "USE_EMAIL" => 1,
            "SOCIETE" => [
                "IDSOCIETE" => $company->getId(),
                "LIBELLE" => $company->getName(),
                "SIRET" => $company->getCompanyNumber(),
                "TVA_INTRA" => $company->getVatNumber(),
                "IDMODEPAIEMENT" => 6,
                "IDECHEANCE" => 9,
            ],
            "CONTACTS" => [
                $addAddress($order->getBillingAddress(), "billing"),
                $addAddress($order->getBillingAddress(), "other"),
                $addAddress($order->getDeliveryAddress(), "delivery"),
            ]
        ];
    }

    (new JsonResponse($response, JsonResponse::HTTP_OK, ["Access-Control-Allow-Origin" => "*"]))->send();
}
