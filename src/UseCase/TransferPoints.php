<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Entity\Key\Credit;
use App\Entity\Key\Debit;
use App\Entity\Key\Transfer;
use App\Entity\Key\Wallet;
use App\Repository\Key\WalletRepository;

class TransferPoints implements TransferPointsInterface
{
    /**
     * @var WalletRepository<Wallet> $walletRepository
     */
    private WalletRepository $walletRepository;

    /**
     * @param WalletRepository<Wallet> $walletRepository
     */
    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function execute(Transfer $transfer): void
    {
        $points = $transfer->getPoints();

        /** @var Wallet $remainingWallet */
        foreach ($transfer->getFrom()->getRemainingWallets() as $remainingWallet) {
            $pointsToDebit = $remainingWallet->getBalance() < $points
                ? $remainingWallet->getBalance()
                : $points;

            $debit = new Debit($remainingWallet, -$pointsToDebit, $transfer);

            $transfer->getTransactions()->add($debit);

            $wallet = $this->walletRepository->findOneByAccountAndPurchase(
                $transfer->getTo(),
                $remainingWallet->getPurchase()
            );

            if ($wallet === null) {
                $wallet = new Wallet($transfer->getTo(), $debit->getWallet()->getExpiredAt());
                $wallet->setPurchase($remainingWallet->getPurchase());
            }

            $credit = new Credit($wallet, $pointsToDebit, $transfer);

            $transfer->getTransactions()->add($credit);

            $points -= $pointsToDebit;

            if ($points === 0) {
                break;
            }
        }
    }
}
