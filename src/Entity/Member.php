<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Member extends Company
{
    /**
     * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="members")
     */
    private ?Organization $organization = null;

    /**
     * @var Collection<int, Client>
     * @ORM\OneToMany(targetEntity=Client::class, mappedBy="member")
     */
    private Collection $clients;

    public static function getType(): string
    {
        return "Adhérent";
    }

    public function __construct()
    {
        $this->clients = new ArrayCollection();
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization): self
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }
}
