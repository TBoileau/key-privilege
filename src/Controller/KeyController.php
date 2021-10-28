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
use App\Form\Key\GiveType;
use App\Form\Key\PurchaseType;
use App\Form\Key\ReturnType;
use App\Form\Key\TransferType;
use App\Pdf\Generator;
use App\Repository\Key\AccountRepository;
use App\Repository\Key\TransactionRepository;
use App\UseCase\TransferPointsInterface;
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
     * @param AccountRepository<Account> $accountRepository
     * @Route("/", name="key_index")
     */
    public function index(AccountRepository $accountRepository, Request $request): Response
    {
        /** @var SalesPerson|Manager $user */
        $user = $this->getUser();

        $accounts = [];

        if ($this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_SALES_PERSON')) {
            if ($request->query->get("field") === null) {
                $request->query->set("field", 'a.createdAt');
            }

            if ($request->query->get("direction") === null) {
                $request->query->set("direction", 'desc');
            }

            $accounts = $accountRepository->getAccountsByEmployee(
                $user,
                $request->query->getInt("page", 1),
                10,
                $request->query->get("field"),
                $request->query->get("direction"),
                $request->query->get("filter"),
            );
        }


        return $this->render("ui/key/index.html.twig", [
            "accounts" => $accounts,
            "pages" => ceil(count($accounts) / 10),
        ]);
    }

    /**
     * @param TransactionRepository<Transaction> $transactionRepository
     * @Route("/transactions", name="key_transactions")
     */
    public function transactions(TransactionRepository $transactionRepository, Request $request): Response
    {
        /** @var SalesPerson|Manager $user */
        $user = $this->getUser();

        if ($request->query->get("field") === null) {
            $request->query->set("field", 't.createdAt');
        }

        if ($request->query->get("direction") === null) {
            $request->query->set("direction", 'desc');
        }

        $transactions = $transactionRepository->getTransactionsByEmployee(
            $user,
            $request->query->getInt("page", 1),
            10,
            $request->query->get("field"),
            $request->query->get("direction"),
            $request->query->get("filter"),
        );

        return $this->render("ui/key/transactions.html.twig", [
            "transactions" => $transactions,
            "pages" => ceil(count($transactions) / 10),
        ]);
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
     * @Route("/don-de-cles", name="key_give")
     * @IsGranted("ROLE_KEY_TRANSFER")
     */
    public function give(Request $request, TransferPointsInterface $transferPoints): Response
    {
        /** @var Manager $manager */
        $manager = $this->getUser();

        $transfer = new Transfer();

        $form = $this->createForm(GiveType::class, $transfer, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transferPoints->execute($transfer);
            $this->getDoctrine()->getManager()->persist($transfer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                "Le don de clés a été effectué avec succès."
            );
            return $this->redirectToRoute("key_index");
        }

        return $this->render("ui/key/give.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/retrocession-de-cles", name="key_return")
     * @IsGranted("ROLE_KEY_TRANSFER")
     */
    public function return(Request $request, TransferPointsInterface $transferPoints): Response
    {
        /** @var Manager $manager */
        $manager = $this->getUser();

        $transfer = new Transfer();

        $form = $this->createForm(ReturnType::class, $transfer, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transferPoints->execute($transfer);
            $this->getDoctrine()->getManager()->persist($transfer);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                "success",
                "La rétrocession de clés a été effectuée avec succès."
            );
            return $this->redirectToRoute("key_index");
        }

        return $this->render("ui/key/return.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/acheter", name="key_purchase")
     * @IsGranted("ROLE_KEY_PURCHASE")
     */
    public function purchase(
        Request $request,
        MailerInterface $mailer,
        Generator $generator,
        string $publicDir
    ): Response {
        /** @var Manager $manager */
        $manager = $this->getUser();

        if ($manager->getDeliveryAddress() === null || $manager->getMember()->getBillingAddress() === null) {
            $this->addFlash(
                'danger',
                'Vous devez avoir saisi au moins une adresse de facturation et de livraison.'
            );
            return $this->redirectToRoute('address_list');
        }

        $purchase = new Purchase();

        $purchase->setManager($manager);

        if ($manager->getMembers()->count() === 1) {
            $purchase->setAccount($manager->getMember()->getAccount());
        }

        $form = $this->createForm(PurchaseType::class, $purchase, ["manager" => $manager])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchase->prepare();
            $this->getDoctrine()->getManager()->persist($purchase);
            $this->getDoctrine()->getManager()->flush();
            $filename = $generator->generate(
                $purchase->getReference(),
                'ui/key/pdf.html.twig',
                ['purchase' => $purchase]
            );
            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->to(new Address("contact@keyprivilege.fr", "Key Privilege"))
                    ->htmlTemplate("emails/key_purchase.html.twig")
                    ->context(["purchase" => $purchase])
                    ->attachFromPath(sprintf('%s/%s', $publicDir, $filename))
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
}
