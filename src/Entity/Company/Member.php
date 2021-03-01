<?php

declare(strict_types=1);

namespace App\Entity\Company;

use App\Entity\User\Collaborator;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Repository\Company\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MemberRepository::class)
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

    /**
     * @var Collection<int, Collaborator>
     * @ORM\OneToMany(targetEntity=Collaborator::class, mappedBy="member")
     */
    private Collection $collaborators;

    /**
     * @var Collection<int, Manager>
     * @ORM\ManyToMany(targetEntity=Manager::class, mappedBy="members")
     */
    private Collection $managers;

    /**
     * @var Collection<int, SalesPerson>
     * @ORM\OneToMany(targetEntity=SalesPerson::class, mappedBy="member")
     */
    private Collection $salesPersons;

    public static function getType(): string
    {
        return "Adhérent";
    }

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->managers = new ArrayCollection();
        $this->collaborators = new ArrayCollection();
        $this->salesPersons = new ArrayCollection();
    }

    public function getOrganization(): ?Organization
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

    /**
     * @return Collection<int, Collaborator>
     */
    public function getCollaborators(): Collection
    {
        return $this->collaborators;
    }

    /**
     * @return Collection<int, Manager>
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    /**
     * @return Collection<int, SalesPerson>
     */
    public function getSalesPersons(): Collection
    {
        return $this->salesPersons;
    }
}
