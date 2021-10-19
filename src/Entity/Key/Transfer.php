<?php

declare(strict_types=1);

namespace App\Entity\Key;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=true)
 */
class Transfer
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Transaction>
     * @ORM\ManyToMany(targetEntity=Transaction::class, cascade={"persist"})
     * @ORM\JoinTable(name="transfer_transactions")
     */
    private Collection $transactions;

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     * @ORM\Column(type="integer")
     */
    private int $points;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class)
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    private Account $from;

    /**
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity=Account::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Account $to;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $comment = null;

    public function __construct()
    {
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

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function getFrom(): Account
    {
        return $this->from;
    }

    public function setFrom(Account $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): Account
    {
        return $this->to;
    }

    public function setTo(Account $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): Transfer
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->from === $this->to) {
            $context->buildViolation('You cannot transfer points between same account.')->addViolation();
        }

        if ($this->from->getBalance() < $this->points) {
            $context->buildViolation('The amount of point cannot be less than account balance.')->addViolation();
        }
    }
}
