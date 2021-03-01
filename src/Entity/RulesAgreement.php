<?php

namespace App\Entity;

use App\Entity\User\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RulesAgreement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="rulesAgreements")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Rules::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Rules $rules;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $accepted;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $agreedAt;

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getRules(): Rules
    {
        return $this->rules;
    }

    public function setRules(Rules $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;
        return $this;
    }

    public function getAgreedAt(): DateTimeImmutable
    {
        return $this->agreedAt;
    }

    public function setAgreedAt(DateTimeImmutable $agreedAt): self
    {
        $this->agreedAt = $agreedAt;
        return $this;
    }
}
