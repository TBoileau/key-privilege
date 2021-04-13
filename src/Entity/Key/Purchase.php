<?php

declare(strict_types=1);

namespace App\Entity\Key;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Purchase extends Transaction
{
    public const OPERATION = "ACH";

    public const MODE_CHECK = "Chèque";

    public const MODE_BANK_WIRE = "Virement";

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
}
