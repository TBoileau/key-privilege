<?php

declare(strict_types=1);

namespace App\Entity\Key;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Credit extends Transaction
{
    public const OPERATION = "CRE";

    /**
     * @ORM\OneToOne(targetEntity=Transfer::class)
     */
    private ?Transfer $transfer = null;

    public function __construct(Wallet $wallet, int $points, ?Transfer $transfer = null)
    {
        parent::__construct($wallet, $points);
        $this->transfer = $transfer;
        $this->wallet->addTransaction($this);
    }

    public function getTransfer(): ?Transfer
    {
        return $this->transfer;
    }

    public function getType(): string
    {
        return "Crédit";
    }
}
