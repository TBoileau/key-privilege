<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private string $streetAddress;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $restAddress = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Regex(pattern="/^[A-Za-z0-9]{2}\d{3}$/", message="Code postal invalide.")
     */
    private string $zipCode;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    private string $locality;

    /**
     * @ORM\Column(nullable=true)
     * @Assert\NotBlank(groups={"order"})
     * @Assert\Regex(pattern="/^0\d{9}$/", message="N° de téléphone invalide.", groups={"order"})
     */
    private ?string $phone = null;

    /**
     * @ORM\Column(nullable=true)
     * @Assert\NotBlank(groups={"order"})
     * @Assert\Email(groups={"order"})
     */
    private ?string $email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(string $streetAddress): self
    {
        $this->streetAddress = $streetAddress;
        return $this;
    }

    public function getRestAddress(): ?string
    {
        return $this->restAddress;
    }

    public function setRestAddress(?string $restAddress): self
    {
        $this->restAddress = $restAddress;
        return $this;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function setLocality(string $locality): self
    {
        $this->locality = $locality;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }
}
