<?php

declare(strict_types=1);

namespace App\Entity\Key;

use App\Entity\Address;
use App\Entity\User\Manager;
use App\Repository\Key\PurchaseRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PurchaseRepository::class)
 */
class Purchase extends Transaction
{
    public const OPERATION = "ACH";

    public const MODE_CHECK = "Chèque";

    public const MODE_BANK_WIRE = "Virement";

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, cascade={"persist"})
     */
    private ?Address $deliveryAddress = null;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, cascade={"persist"})
     */
    private ?Address $billingAddress = null;

    /**
     * @ORM\Column
     */
    private string $state = "pending";

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $internReference = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank(groups={"Default", "new"})
     */
    private string $mode;

    /**
     * @ORM\ManyToOne(targetEntity=Manager::class)
     */
    private ?Manager $manager = null;

    public function __construct()
    {
    }

    public function prepare(): self
    {
        $wallet = new Wallet($this->account, new DateTimeImmutable("2 year first day of next month midnight"));
        $this->createdAt = new DateTimeImmutable();
        $this->wallet = $wallet;
        $wallet->setPurchase($this);

        return $this;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(?Manager $manager): Purchase
    {
        $this->manager = $manager;
        return $this;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;
        return $this;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getInternReference(): ?string
    {
        return $this->internReference;
    }

    public function setInternReference(?string $internReference): self
    {
        $this->internReference = $internReference;
        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;
        return $this;
    }

    public function getType(): string
    {
        return "Achat";
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): Purchase
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): Purchase
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function getPriceExcludingTax(): int
    {
        return $this->points * 100;
    }

    public function getTax(): int
    {
        return intval($this->points * 0.2 * 100);
    }

    public function getPriceIncludingTax(): int
    {
        return $this->getPriceExcludingTax() + $this->getTax();
    }

    public function getReference(): string
    {
        if ($this->getAccount()->getMember() === null) {
            return parent::getReference();
        }
        return sprintf("BCP%06d-%d", $this->id, $this->getAccount()->getMember()->getId());
    }
}
