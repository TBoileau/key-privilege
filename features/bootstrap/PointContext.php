<?php

declare(strict_types=1);

namespace App\Features;

use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use App\Entity\Key\Transfer;
use App\Repository\Key\WalletRepository;
use App\UseCase\TransferPoints;
use App\UseCase\TransferPointsInterface;
use Behat\Behat\Context\Context;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class PointContext implements Context
{
    /**
     * @var array<Account>
     */
    private array $accounts;

    private TransferPointsInterface $transfer;

    public function __construct()
    {
        /** @var WalletRepository|MockObject $walletRepository */
        $walletRepository = (new Generator())->getMock(WalletRepository::class, [], [], '', false);
        $walletRepository->method("findOneByAccountAndPurchase")->willReturn(null);

        $this->transfer = new TransferPoints($walletRepository);
    }

    /**
     * @Given /^I create (\w+) account$/
     */
    public function accountWithPoints(string $ref)
    {
        $this->accounts[$ref] = new Account();
    }

    /**
     * @Given /^(.+), (\w+) account purchase (\d+) points$/
     */
    public function monthAgoFirstAccountPurchasePoints(string $time, string $account, int $points)
    {
        $purchase = (new Purchase())
            ->setAccount($this->accounts[$account])
            ->setMode(Purchase::MODE_CHECK)
            ->setState("accepted")
            ->setPoints($points)
            ->prepare();

        $reflectionClass = new ReflectionClass($purchase->getWallet());
        $createdAt = $reflectionClass->getProperty('createdAt');
        $createdAt->setAccessible(true);
        $createdAt->setValue($purchase->getWallet(), new DateTimeImmutable($time));
        $expiredAt = $reflectionClass->getProperty('expiredAt');
        $expiredAt->setAccessible(true);
        $expiredAt->setValue(
            $purchase->getWallet(),
            new DateTimeImmutable(sprintf("%s + %s", $time, "2 year first day of next month midnight"))
        );

        $purchase->getWallet()->addTransaction($purchase);
    }

    /**
     * @When /^I transfer (\d+) points from (\w+) account to (\w+) account$/
     */
    public function iTransferPoints(int $points, string $from, string $to)
    {
        $transfer = new Transfer();
        $transfer->setPoints($points);
        $transfer->setTo($this->accounts[$to]);
        $transfer->setFrom($this->accounts[$from]);
        $this->transfer->execute($transfer);
    }

    /**
     * @When /^I purchase (\d+) points for (\w+) account$/
     */
    public function iPurchasePoints(int $points, string $account)
    {
        $purchase = (new Purchase())
            ->setAccount($this->accounts[$account])
            ->setMode(Purchase::MODE_CHECK)
            ->setState("accepted")
            ->setPoints($points)
            ->prepare();
        $purchase->getWallet()->addTransaction($purchase);
    }

    /**
     * @Then /^(\w+) account balance is (\d+) points$/
     */
    public function accountBalance(string $account, int $points)
    {
        Assert::assertEquals($points, $this->accounts[$account]->getBalance());
    }
}
