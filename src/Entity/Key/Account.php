<?php

declare(strict_types=1);

namespace App\Entity\Key;

use App\Entity\Company\Member;
use App\Entity\User\User;
use App\Repository\Key\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AccountRepository::class)
 * @UniqueEntity("reference")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=true)
 */
class Account implements Stringable
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Groups({"read"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"read"})
     */
    private DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Wallet>
     * @ORM\OneToMany(targetEntity=Wallet::class, mappedBy="account", cascade={"persist"})
     * @ORM\OrderBy({"createdAt" : "asc"})
     * @Groups({"read"})
     */
    private Collection $wallets;

    /**
     * @var Collection<int, Transaction>
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="account", cascade={"persist"})
     * @ORM\OrderBy({"createdAt" : "asc"})
     */
    private Collection $transactions;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="account")
     */
    private ?User $user;

    /**
     * @ORM\OneToOne(targetEntity=Member::class, mappedBy="account")
     */
    private ?Member $member = null;

    public function __construct()
    {
        $this->wallets = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Wallet>
     */
    public function getWallets(): Collection
    {
        return $this->wallets;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @return Collection<int, Wallet>
     */
    public function getExpiredWallets(): Collection
    {
        return $this->wallets->filter(fn (Wallet $wallet) => $wallet->isExpired() && $wallet->getBalance() > 0);
    }

    /**
     * @return Collection<int, Wallet>
     */
    public function getRemainingWallets(): Collection
    {
        return $this->wallets->filter(fn (Wallet $wallet) => !$wallet->isExpired() && $wallet->getBalance() > 0);
    }

    public function getBalance(): int
    {
        return intval(
            array_sum(
                $this->getRemainingWallets()->map(fn (Wallet $wallet) => $wallet->getBalance())->toArray()
            )
        );
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function getOwner(): User | Member
    {
        return $this->user ?? $this->member;
    }

    public function __toString(): string
    {
        if ($this->user !== null) {
            return sprintf("Utilisateur : %s", $this->user->getFullName());
        }
        return sprintf("Société : %s", $this->member->getName());
    }

    public function setMember(?Member $member): Account
    {
        $this->member = $member;
        return $this;
    }
}
