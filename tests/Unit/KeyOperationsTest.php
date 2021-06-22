<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use App\Entity\Key\Transfer;
use App\Repository\Key\WalletRepository;
use App\UseCase\TransferPoints;
use App\UseCase\TransferPointsInterface;
use PHPUnit\Framework\TestCase;

class KeyOperationsTest extends TestCase
{
    private Account $firstAccount;

    private Account $secondAccount;

    private TransferPointsInterface $transfer;

    public function setUp(): void
    {
        $walletGateway = $this->createMock(WalletRepository::class);
        $walletGateway->method("findOneByAccountAndPurchase")->willReturn(null);

        $this->transfer = new TransferPoints($walletGateway);

        $this->firstAccount = new Account();

        $this->secondAccount = new Account();
    }

    /**
     * @test
     */
    public function successOperations(): void
    {
        $purchase = (new Purchase())
            ->setAccount($this->firstAccount)
            ->setMode(Purchase::MODE_CHECK)
            ->setState("accepted")
            ->setPoints(2000)
            ->prepare();

        $purchase->getWallet()->addTransaction($purchase);

        $this->assertBalanceEquals($this->firstAccount, 2000);

        $this->transfer->execute(
            (new Transfer())
                ->setFrom($this->firstAccount)
                ->setTo($this->secondAccount)
                ->setPoints(1000)
        );

        $this->assertBalanceEquals($this->firstAccount, 1000);

        $this->assertBalanceEquals($this->secondAccount, 1000);

        $this->transfer->execute(
            (new Transfer())
                ->setFrom($this->secondAccount)
                ->setTo($this->firstAccount)
                ->setPoints(500)
        );

        $this->assertBalanceEquals($this->firstAccount, 1500);

        $this->assertBalanceEquals($this->secondAccount, 500);

        $purchase = (new Purchase())
            ->setAccount($this->secondAccount)
            ->setMode(Purchase::MODE_CHECK)
            ->setState("accepted")
            ->setPoints(1000)
            ->prepare();

        $purchase->getWallet()->addTransaction($purchase);

        $this->assertBalanceEquals($this->secondAccount, 1500);

        $this->transfer->execute(
            (new Transfer())
                ->setFrom($this->secondAccount)
                ->setTo($this->firstAccount)
                ->setPoints(1200)
        );

        $this->assertBalanceEquals($this->firstAccount, 2700);

        $this->assertBalanceEquals($this->secondAccount, 300);
    }

    private function assertBalanceEquals(Account $account, int $balance): void
    {
        $this->assertEquals($balance, $account->getBalance());
    }
}
