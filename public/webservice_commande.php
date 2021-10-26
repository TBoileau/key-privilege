<?php

use App\Entity\Address;
use App\Entity\Key\Credit;
use App\Entity\Key\Purchase;
use App\Entity\Key\Wallet;
use App\Entity\Order\Line;
use App\Entity\Order\Order;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Employee;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Kernel;
use App\Repository\Key\PurchaseRepository;
use App\Repository\Order\LineRepository;
use App\Repository\Order\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address as Addr;
use Symfony\Component\Workflow\WorkflowInterface;

ini_set("display_errors", 1);
ini_set("memory_limit", "512M");
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Europe/Paris');

require '../vendor/fergusean/nusoap/lib/nusoap.php';
require __DIR__ . "/../build/bootstrap.php";

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

/** @var EntityManagerInterface $entityManager */
$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

$server = new nusoap_server;
$server->register('pushCommande');
$server->register('pushFacture');

/**
 * @param array<array-key, array<string, mixed> $request
 * @return array<string, int>
 */
function pushCommande(array $request): array
{
    global $entityManager;

    $response = ["CMD" => 0, "TRACKING" => 0, "EMAIL" => 0, "DOC" => []];

    if (!empty($orders)) {
        foreach ($request as $requestOrder) {
            if ((int) $requestOrder['IDCOMPTA'] < 100000) {
                /** @var OrderRepository $orderRepository */
                $orderRepository = $entityManager->getRepository(Order::class);

                /** @var LineRepository $lineRepository */
                $lineRepository = $entityManager->getRepository(Line::class);

                /** @var Order $order */
                $order = $orderRepository->find((int) $requestOrder['IDCOMPTA']);

                if ($order === null) {
                    continue;
                }

                /** @var array<array-key, Line> $orderLinesCanceled */
                $linesCanceled = [];

                foreach ($requestOrder['Tracking'] ?? [] as $tracking) {
                    if (in_array($tracking['IDSTATUTLIVR'], [2, 9])) {
                        $linesCanceled[] = $lineRepository->find((int) $tracking['IDBDCELT_CLIENT']);
                    }
                }

                foreach ($requestOrder['Expedition'] ?? [] as $delivery) {
                    if ($delivery["LIVRE"]) {
                        $order->setState("delivered");
                    } else {
                        $order->setState("on_delivery");
                    }
                }

                if (count($linesCanceled) === $order->getLines()->count()) {
                    $order->setState("canceled");
                }

                foreach ($linesCanceled as $line) {
                    $wallet = new Wallet(
                        $order->getUser()->getAccount(),
                        new DateTimeImmutable("2 year first day of next month midnight")
                    );

                    $credit = new Credit($wallet, $line->getTotal());
                    $credit->setOrder($order);
                    $entityManager->persist($wallet);
                    $entityManager->flush();
                }
            }
        }
    }

    return $response;
}

/**
 * @param array<string, mixed> $invoice
 * @return array<string, int>
 */
function pushFacture(array $invoice): array
{
    global $kernel, $entityManager;

    $response = ["FAC" => 0, "CREDIT" => 0, "ELT" => 0, "DEP" => 0, "EMAIL" => 0, "DOC" => 0];

    $pdfDir = __DIR__ . '/pdf';
    $pdfFilename = $invoice['NUM'] . '.pdf';
    if (is_file($pdfFilename)) {
        if (copy($pdfFilename, sprintf('%s/%s', $pdfDir, $pdfFilename))) {
            $response['DOC']++;
            $response['FAC']++;
            unlink($pdfFilename);
        }
    }

    /** @var WorkflowInterface $stateMachine */
    $stateMachine = $kernel->getContainer()->get('state_machine.purchase');

    if (isset($invoice['Dependances'])) {
        foreach ($invoice['Dependances'] as $dependency) {
            if (intval($dependency['IDBDC']) > 100000) {
                /** @var Purchase $purchase */
                $purchase = $entityManager->find(Purchase::class, intval($dependency['IDBDC']) - 100000);
                if (in_array(intval($invoice['IDFACTURE_STA']), [3, 4])) {
                    $stateMachine->apply($purchase, "accept");
                    $entityManager->flush();
                    $response['CREDIT']++;
                } elseif (intval($invoice['IDFACTURE_STA']) === 5) {
                    $stateMachine->apply($purchase, "cancel");
                    $entityManager->flush();
                }
            }
        }
    }

    if (isset($invoice['Emails'])) {
        foreach ($invoice['Emails'] as $emailData) {
            try {
                $email = (new TemplatedEmail())
                    ->from(new Addr("logistique@keyprivilege.fr", "Key Privilege - Logistique"))
                    ->to(
                        ...array_map(
                            static fn (string $email) => new Addr($email),
                            explode(";", $emailData['EMAIL_DEST'])
                        )
                    )
                    ->cc(
                        ...array_map(
                            static fn (string $email) => new Addr($email),
                            explode(";", $emailData['EMAIL_CC'])
                        )
                    )
                    ->bcc(
                        ...array_map(
                            static fn (string $email) => new Addr($email),
                            explode(";", $emailData['EMAIL_BCC'])
                        )
                    )
                    ->subject($emailData['OBJET'])
                    ->htmlTemplate('emails/invoice.html.twig')
                    ->context(['emssage' => nl2br($emailData['TEXTE'])]);

                /** @var Mailer $mailer */
                $mailer = $kernel->getContainer()->get('mailer.mailer');
                $mailer->send($email);
                $response['EMAIL']++;
            } catch (TransportExceptionInterface) {
            }
        }
    }
    return $response;
}

$request = Request::createFromGlobals();

function orderToArray(Order $order): array
{
    $valeur = 0;
    $totalHT = 0;
    $totalTTC = 0;
    $totalTVA = 0;
    foreach ($order->getLines() as $line) {
        $valeur += $line->getAmount() * $line->getQuantity();
        $totalHT += $line->getSalePrice();
    }
    $totalTTC = $totalHT * 1.2;
    $totalTVA = $totalTTC - $totalHT;

    $response = [
        "IDBDC" => strval($order->getId()),
        "IDUSER" => strval($order->getUser()->getId()),
        "NUM" => "BCB" . $order->getId() . "-" . $order->getUser()->getId(),
        "DATE" => $order->getCreatedAt()->format('Y-m-d H:i:s'),
        "NUMCLIENT" => "CL" . $order->getUser()->getId(),
        "REFBDCCLI" => null,
        "REFBDCSQAE" => "",
        "IDCONTACT_FAC" => strval($order->getBillingAddress()->getId()),
        "IDCONTACT_LIV" => strval($order->getDeliveryAddress()->getId()),
        "IDCONTACT_AUT" => null,
        "VALEUR," => strval($valeur),
        "IDBDC_TYP" => "1",
        "IDMODEPAIEMENT" => null,
        "TOTALHT" => strval($totalHT),
        "TOTALTTC" => strval($totalTTC),
        "TOTALTVA" => strval($totalTVA),
        "IDPROJET" => "178",
        "DOCS" => 'pdf/' . sprintf("BCB%06d-%d",  $order->getId(), $order->getUser()->getId()) . '.pdf',
        'LIGNES' => [],
        'USERS' => []
    ];


    /** @var Line $line */
    foreach ($order->getLines() as $line) {
        $response["LIGNES"][] = [
            "IDBDC_ELT" => strval($line->getId()),
            "IDPRODUIT" => strval($line->getProduct()->getId()),
            "PAUHT" => strval($line->getPurchasePrice()),
            "PVUHT" => strval($line->getSalePrice()),
            "VALEUR" => strval($line->getAmount()),
            "PPGC" => strval($line->getPurchasePrice()),
            "IDBDC" => strval($order->getId()),
            "QTE" => strval($line->getQuantity()),
            "REFERENCE" => strval($line->getProduct()->getReference()),
            "DESIGN" => $line->getProduct()->getDescription(),
            "MONTANT" => strval($line->getSalePrice() * $line->getQuantity()),
            "IDTVA" => strval($line->getVat()),
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
        "IDCONTACT" => strval($address->getId()),
        "IDPROPRIETE" => $property,
        "VALEUR" => $value,
        "IDVALEUR" => 0
    ];

    $addAddress = static fn (Address $address, string $type): array => [
        "IDCONTACT" => strval($address->getId()),
        "IDUSER" => strval($order->getUser()->getId()),
        "IDTYPE" =>  match ($type) {
            "delivery" => "3",
            "billing" => "2",
            "other" => "1"
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

    $response["USERS"][0] = [
        "IDUSER" => strval($order->getUser()->getId()),
        "NOM" => $order->getUser()->getLastName(),
        "PRENOM" => $order->getUser()->getFirstName(),
        "IDSOC" => strval($company->getId()),
        "IDPROFIL" => match ($order->getUser()::class) {
            Manager::class => "2",
            SalesPerson::class => "3",
            Collaborator::class => "5",
            Customer::class => "4",
        },
        "ACTIF" => "1",
        "EMAIL" => $order->getUser()->getEmail(),
        "USE_EMAIL" => "1",
        "SOCIETE" => [
            "IDSOCIETE" => strval($company->getId()),
            "LIBELLE" => $company->getName(),
            "SIRET" => $company->getCompanyNumber(),
            "TVA_INTRA" => $company->getVatNumber(),
            "IDMODEPAIEMENT" => "6",
            "IDECHEANCE" => "9",
        ],
        "CONTACTS" => [
            $order->getDeliveryAddress() ? $addAddress($order->getDeliveryAddress(), "delivery") : $addAddress($company->getAddress(), "delivery"),
            $order->getBillingAddress() ? $addAddress($order->getBillingAddress(), "other") : $addAddress($company->getAddress(), "other"),
            $order->getBillingAddress() ? $addAddress($order->getBillingAddress(), "billing") : $addAddress($company->getAddress(), "billing"),
        ]
    ];

    return $response;
}

function purchaseToArray(Purchase $purchase): array
{
    $response = [
        "IDBDC" => strval(100000 + $purchase->getId()),
        "IDUSER" => strval($purchase->getManager()->getId()),
        "NUM" => "BCB" . $purchase->getId() . "-" . $purchase->getManager()->getId(),
        "DATE" => $purchase->getCreatedAt()->format('Y-m-d H:i:s'),
        "NUMCLIENT" => "CL" . $purchase->getManager()->getId(),
        "REFBDCCLI" => null,
        "REFBDCSQAE" => "",
        "IDCONTACT_FAC" => strval($purchase->getBillingAddress()->getId()),
        "IDCONTACT_LIV" => strval($purchase->getDeliveryAddress()->getId()),
        "IDCONTACT_AUT" => null,
        "VALEUR," => strval($purchase->getPoints()),
        "IDBDC_TYP" => "1",
        "IDMODEPAIEMENT" => null,
        "TOTALHT" => strval($purchase->getPoints()),
        "TOTALTTC" => strval($purchase->getPoints() * 1.2),
        "TOTALTVA" => strval(($purchase->getPoints() * 1.2) - $purchase->getPoints()),
        "IDPROJET" => "178",
        "DOCS" => 'pdf/' . sprintf("BCP%06d-%d",  $purchase->getId(), $purchase->getManager()->getId()) . '.pdf',
        'LIGNES' => [],
        'USERS' => []
    ];
    $response["LIGNES"][] = [
        "IDBDC_ELT" => strval(100000 + $purchase->getId()),
        "IDPRODUIT" => strval(23371),
        "PAUHT" => strval(1),
        "PVUHT" => strval(1),
        "VALEUR" => strval(1),
        "PPGC" => strval(1.2),
        "IDBDC" => strval(100000 + $purchase->getId()),
        "QTE" => strval($purchase->getPoints()),
        "REFERENCE" => 'CLE1',
        "DESIGN" => 'Achat de clés dans le cadre du programe Key Privilege',
        "MONTANT" => strval($purchase->getPoints()),
        "IDTVA" => strval(1),
    ];

    /** @var Manager $user */
    $user = $purchase->getManager();

    $company = $user->getMember();

    $addDetail = static fn (Address $address, int $property, mixed $value) => [
        "IDCONTACT" => strval($address->getId()),
        "IDPROPRIETE" => $property,
        "VALEUR" => $value,
        "IDVALEUR" => 0
    ];

    $addAddress = static fn (Address $address, string $type): array => [
        "IDCONTACT" => strval($address->getId()),
        "IDUSER" => strval($user->getId()),
        "IDTYPE" =>  match ($type) {
            "delivery" => "3",
            "billing" => "2",
            "other" => "1"
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
            $addDetail($address, 17, ""),
            $addDetail($address, 18, ""),
            $addDetail($address, 19, ""),
        ]
    ];

    $response["USERS"][0] = [
        "IDUSER" => strval($user->getId()),
        "NOM" => $user->getLastName(),
        "PRENOM" => $user->getLastName(),
        "IDSOC" => strval($company->getId()),
        "IDPROFIL" => "2",
        "ACTIF" => "1",
        "EMAIL" => $user->getEmail(),
        "USE_EMAIL" => "1",
        "SOCIETE" => [
            "IDSOCIETE" => strval($company->getId()),
            "LIBELLE" => $company->getName(),
            "SIRET" => $company->getCompanyNumber(),
            "TVA_INTRA" => $company->getVatNumber(),
            "IDMODEPAIEMENT" => "6",
            "IDECHEANCE" => "9",
        ],
        "CONTACTS" => [
            $addAddress($purchase->getBillingAddress(), "billing"),
            $addAddress($purchase->getBillingAddress(), "other"),
            $addAddress($purchase->getDeliveryAddress(), "delivery"),
        ]
    ];

    return $response;
}
header('Access-Control-Allow-Origin: *');

if ($request->isMethod(Request::METHOD_GET) && $request->get("ACTION") === "getCommandes") {
    $action = $_GET["ACTION"] ?? $_POST["ACTION"];

    /** @var OrderRepository $orderRepository */
    $orderRepository = $entityManager->getRepository(Order::class);

    /** @var array<array-key, Order> $orders */
    $orders = $orderRepository->findBy(['state' => 'pending']);

    /** @var array<int, array<string, array<string, mixed>>> $response */
    $response = [];

    $row = 0;

    foreach ($orders as $order) {
        $response[$row] = orderToArray($order);
        $row++;
    }

    /** @var PurchaseRepository $purchaseRepository */
    $purchaseRepository = $entityManager->getRepository(Purchase::class);

    /** @var array<array-key, Purchase> $purchases */
    $purchases = $purchaseRepository->findBy(['state' => 'pending']);

    foreach ($purchases as $purchase) {
        $response[$row] = purchaseToArray($purchase);
        $row++;
    }
    echo json_encode($response);
    //(new JsonResponse($response, JsonResponse::HTTP_OK, ["Access-Control-Allow-Origin" => "*"]))->send();
} else {
    $server->service($HTTP_RAW_POST_DATA ?? '');
}
