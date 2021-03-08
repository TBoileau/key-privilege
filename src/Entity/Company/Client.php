<?php

declare(strict_types=1);

namespace App\Entity\Company;

use App\Entity\User\Customer;
use App\Entity\User\SalesPerson;
use App\Repository\Company\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 * @Assert\Expression(
 *      expression="this.getSalesPerson() !== null and this.getSalesPerson().getMember() === this.getMember()",
 *      message="Le/la commercial(e) rattaché(e) n'appartient à l'adhérent sélectionné."
 * )
 */
class Client extends Company
{
    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="clients")
     */
    private ?Member $member = null;

    /**
     * @ORM\ManyToOne(targetEntity=SalesPerson::class, inversedBy="clients")
     */
    private ?SalesPerson $salesPerson = null;

    /**
     * @var Collection<int, Customer>
     * @ORM\OneToMany(targetEntity=Customer::class, mappedBy="client")
     */
    private Collection $customers;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $manualDelivery = false;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
    }

    public static function getType(): string
    {
        return "Client";
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    public function getSalesPerson(): ?SalesPerson
    {
        return $this->salesPerson;
    }

    public function setSalesPerson(?SalesPerson $salesPerson): Client
    {
        $this->salesPerson = $salesPerson;
        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function isManualDelivery(): bool
    {
        return $this->manualDelivery;
    }

    public function setManualDelivery(bool $manualDelivery): self
    {
        $this->manualDelivery = $manualDelivery;
        return $this;
    }
}
