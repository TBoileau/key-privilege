<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Entity\Shop\Product;
use App\Entity\User\User;
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

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->lines = new ArrayCollection();
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
            $this->lines->add($line);
        }

        $line->increaseQuantity();

        return $this;
    }
}
