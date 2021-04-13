<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use App\Entity\Key\Transaction;
use App\Entity\Key\Transfer;
use App\Entity\Key\Wallet;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use App\Form\Key\PurchaseType;
use App\Form\Key\TransferType;
use App\Repository\Key\AccountRepository;
use App\UseCase\TransferPointsInterface;
use Couchbase\WildcardSearchQuery;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * @Route("/cles")
 */
class KeyController extends AbstractController
{
    /**
     * @Route("/", name="key_index")
     */
    public function index(): Response
    {
        return $this->render("ui/key/index.html.twig");
    }

    /**
     * @Route("/{id}/export", name="key_export")
     */
    public function export(Account $account, string $tempDir): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Mouvements de clés");
        $sheet->fromArray(
            array_merge(
                [["Date", "Opération", "Clés"]],
                $account->getTransactions()->map(fn (Transaction $transaction) => [
                    $transaction->getCreatedAt()->format("d/m/Y"),
                    $transaction->getType(),
                    sprintf("%d clés", $transaction->getPoints())
                ])->toArray()
            )
        );

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle("Expiration de vos clés");
        $sheet->fromArray(
            array_merge(
                [["Date d'acquisition", "Date d'expiration", "Clés restantes"]],
                $account->getRemainingWallets()->map(fn (Wallet $wallet) => [
                    $wallet->getCreatedAt()->format("d/m/Y"),
                    $wallet->getExpiredAt()->format("d/m/Y"),
                    sprintf("%d clés", $wallet->getBalance())
                ])->toArray()
            )
        );

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle("Clés expirés");
        $sheet->fromArray(
            array_merge(
                [["Date d'acquisition", "Date d'expiration", "Clés restantes"]],
                $account->getExpiredWallets()->map(fn (Wallet $wallet) => [
                    $wallet->getCreatedAt()->format("d/m/Y"),
                    $wallet->getExpiredAt()->format("d/m/Y"),
                    sprintf("%d clés", $wallet->getBalance())
                ])->toArray()
            )
        );

        $filename = sprintf("export_cles_%s.xlsx", (string) Uuid::v4());

        $writer = new Xlsx($spreadsheet);
        $writer->save(sprintf("%s/%s", $tempDir, $filename));


        return $this->file(sprintf("%s/%s", $tempDir, $filename));
    }

    /**
     * @Route("/historique/{id}", name="key_history")
     */
    public function history(Account $account): Response
    {
        return $this->render("ui/key/history.html.twig", [
            "account" => $account
        ]);
    }

    /**
     * @Route("/transferer", name="key_transfer")
     * @IsGranted("ROLE_KEY_TRANSFER")
     */
    public function transfer(Request $request, TransferPointsInterface $transferPoints): Response
    {
        /** @var Manager $manager */
        $manager = $this->getUser();

        $transfer = new Transfer();

        $form = $this->createForm(TransferType::class, $transfer, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transferPoints->execute($transfer);
            $this->getDoctrine()->getManager()->persist($transfer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                "Le transfert de clés a été effectué avec succès."
            );
            return $this->redirectToRoute("key_index");
        }

        return $this->render("ui/key/transfer.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/acheter", name="key_purchase")
     * @IsGranted("ROLE_KEY_PURCHASE")
     */
    public function purchase(Request $request, MailerInterface $mailer): Response
    {
        /** @var Manager $manager */
        $manager = $this->getUser();

        $purchase = new Purchase();

        if ($manager->getMembers()->count() === 1) {
            $purchase->setAccount($manager->getMember()->getAccount());
        }

        $form = $this->createForm(PurchaseType::class, $purchase, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchase->prepare();
            $this->getDoctrine()->getManager()->persist($purchase);
            $this->getDoctrine()->getManager()->flush();
            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->to(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->htmlTemplate("emails/key_purchase.html.twig")
                    ->context(["purchase" => $purchase])
            );
            $this->addFlash(
                "success",
                "
                    Votre demande d'achat de clés a été envoyée avec succès. 
                    Dès réception du paiement, les clés vous seront créditées.
                "
            );
            return $this->redirectToRoute("key_index");
        }

        return $this->render("ui/key/purchase.html.twig", ["form" => $form->createView()]);
    }


    /**
     * @param AccountRepository<Account> $accountRepository
     * @Route("/clients", name="key_clients")
     * @Security("is_granted('ROLE_SALES_PERSON') or is_granted('ROLE_MANAGER')")
     */
    public function clients(AccountRepository $accountRepository): Response
    {
        /** @var SalesPerson|Manager $user */
        $user = $this->getUser();

        return $this->render("ui/key/_clients.html.twig", [
            "accounts" => $accountRepository->getClientsAccountByEmployee($user)
        ]);
    }
}
