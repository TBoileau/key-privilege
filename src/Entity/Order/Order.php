<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Entity\Address;
use App\Entity\Key\Transaction;
use App\Entity\Shop\Product;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Employee;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use Cassandra\Custom;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @ORM\Column
     */
    private string $state = "cart";

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @var Collection<int, Line>
     * @ORM\OneToMany(targetEntity=Line::class, mappedBy="order", cascade={"persist"})
     */
    private Collection $lines;

    /**
     * @var Collection<int, Transaction>
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="order")
     */
    private Collection $transactions;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, cascade={"persist"})
     */
    private ?Address $deliveryAddress = null;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, cascade={"persist"})
     */
    private ?Address $billingAddress = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->lines = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @return Collection<int, Line>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addProduct(Product $product): self
    {
        $lines = $this->lines->filter(fn (Line $line) => $line->getProduct() === $product);

        $line = $lines->first();

        if ($line === false) {
            $line = new Line();
            $line->setOrder($this);
            $line->setProduct($product);
            $line->setRetailPrice($product->getRetailPrice());
            $line->setPurchasePrice($product->getPurchasePrice());
            $line->setVat($product->getVat());
            $line->setSalePrice($product->getSalePrice());
            $this->lines->add($line);
        }

        $line->increaseQuantity();

        return $this;
    }

    public function getTotal(): int
    {
        return intval(
            array_sum(
                $this->lines->map(fn (Line $line) => $line->getTotal())->toArray()
            )
        );
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): self
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getNumberOfProducts(): int
    {
        return intval(array_sum($this->lines->map(fn (Line $line) => $line->getQuantity())->toArray()));
    }

    public function getReference(): string
    {
        return sprintf("BCB%06d-%d", $this->id, $this->user->getId());
    }

    public function getCompanyName(): string
    {
        if ($this->user instanceof Customer) {
            /** @var Customer $user */
            $user = $this->user;
            return $user->getClient()->getName();
        }

        /** @var SalesPerson|Manager|Collaborator $user */
        $user = $this->user;
        return $user->getMember()->getName();
    }
}
