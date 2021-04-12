<?php

declare(strict_types=1);

namespace App\Entity\Key;

use App\Entity\Order\Order;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="`transaction`")
 * @UniqueEntity("reference")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"debit"=Debit::class, "credit"=Credit::class, "purchase"=Purchase::class})
 */
abstract class Transaction
{
    public const OPERATION = "TRA";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank
     */
    protected DateTimeImmutable $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "new"})
     */
    protected Account $account;

    /**
     * @ORM\ManyToOne(targetEntity=Wallet::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    protected Wallet $wallet;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(groups={"Default", "new"})
     * @Assert\GreaterThan(0, groups={"Default", "new"})
     */
    protected int $points = 0;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="transactions")
     */
    protected ?Order $order = null;

    abstract public function getType(): string;

    public function __construct(Wallet $wallet, int $points)
    {
        $this->createdAt = new DateTimeImmutable();
        $this->wallet = $wallet;
        $this->account = $wallet->getAccount();
        $this->account->getTransactions()->add($this);
        $this->points = $points;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getReference(): string
    {
        return sprintf("%s - %08d", $this->getType(), $this->id);
    }

    public function __toString(): string
    {
        return $this->getReference();
    }

    public function setOrder(?Order $order): void
    {
        $this->order = $order;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }
}
