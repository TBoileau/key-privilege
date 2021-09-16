<?php

declare(strict_types=1);

namespace App\Repository\Key;

use App\Entity\Key\Account;
use App\Entity\Key\Transaction;
use App\Entity\Key\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function findOneByAccountAndPurchase(Account $account, Transaction $transaction): ?Wallet
    {
        return $this->findOneBy([
            'account' => $account,
            "purchase" => $transaction
        ]);
    }
}
