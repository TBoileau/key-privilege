<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Entity\Shop\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="order_line")
 */
class Line
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="lines")
     * @ORM\JoinColumn(nullable=false)
     */
    private Order $order;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Product $product;

    /**
     * @ORM\Column(type="integer")
     */
    private int $amount;

    /**
     * @ORM\Column(type="integer")
     */
    private int $quantity = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $purchasePrice;

    /**
     * @ORM\Column(type="integer")
     */
    private int $salePrice;

    /**
     * @ORM\Column(type="integer")
     */
    private int $retailPrice;

    /**
     * @ORM\Column(type="integer")
     */
    private int $vat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        $this->amount = $product->getAmount();
        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function decreaseQuantity(): self
    {
        $this->quantity--;
        return $this;
    }

    public function increaseQuantity(): self
    {
        $this->quantity++;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->quantity * $this->amount;
    }

    public function getPurchasePrice(): int
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(int $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice;
        return $this;
    }

    public function getSalePrice(): int
    {
        return $this->salePrice;
    }

    public function setSalePrice(int $salePrice): self
    {
        $this->salePrice = $salePrice;
        return $this;
    }

    public function getRetailPrice(): int
    {
        return $this->retailPrice;
    }

    public function setRetailPrice(int $retailPrice): self
    {
        $this->retailPrice = $retailPrice;
        return $this;
    }

    public function getVat(): int
    {
        return $this->vat;
    }

    public function setVat(int $vat): self
    {
        $this->vat = $vat;
        return $this;
    }
}
