<?php

declare(strict_types=1);

namespace App\Entity\Key;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Stringable;

/**
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=true)
 */
class Wallet implements Stringable
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Purchase::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Purchase $purchase = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $balance = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $expiredAt;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class, fetch="EAGER", inversedBy="wallets")
     * @ORM\JoinColumn(nullable=false)
     */
    private Account $account;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Transaction>
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="wallet", cascade={"persist"})
     * @ORM\OrderBy({"createdAt" : "asc"})
     */
    private Collection $transactions;

    public function __construct(Account $account, DateTimeImmutable $expiredAt)
    {
        $this->transactions = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->expiredAt = $expiredAt;
        $this->account = $account;
        if (!$account->getWallets()->contains($this)) {
            $account->getWallets()->add($this);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function addTransaction(Transaction $transaction): void
    {
        $this->balance += $transaction->getPoints();
    }

    public function getExpiredAt(): ?DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getPurchase(): ?Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(?Purchase $purchase): void
    {
        $this->purchase = $purchase;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isExpired(): bool
    {
        return $this->expiredAt !== null && $this->expiredAt < new DateTimeImmutable();
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function getReference(): string
    {
        return sprintf("%08d", $this->id);
    }

    public function __toString(): string
    {
        return $this->getReference();
    }
}
