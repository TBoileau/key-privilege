<?php

declare(strict_types=1);

namespace App\Entity\Company;

use App\Entity\Address;
use App\Entity\User\Customer;
use App\Entity\User\SalesPerson;
use App\Repository\Company\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
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
     * @ORM\OneToOne(targetEntity=Address::class, cascade={"persist"})
     * @Assert\Valid
     */
    private ?Address $address = null;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->address = new Address();
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

    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->salesPerson !== null and $this->salesPerson->getMember() !== $this->member) {
            $context->buildViolation('Le/la commercial(e) rattaché(e) n\'appartient à l\'adhérent sélectionné.')
                ->atPath("salesPerson")
                ->addViolation();
        }
    }
}
