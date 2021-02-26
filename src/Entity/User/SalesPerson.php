<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Company\Client;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SalesPerson extends User
{
    use Employee;

    /**
     * @var Collection<int, Client>
     * @ORM\OneToMany(targetEntity=Client::class, mappedBy="salesPerson")
     */
    private Collection $clients;

    public function __construct()
    {
        parent::__construct();
        $this->clients = new ArrayCollection();
    }

    public function getRole(): string
    {
        return "ROLE_SALES_PERSON";
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }
}
