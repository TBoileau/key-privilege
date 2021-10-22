<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

/**
 * @ORM\Entity
 */
class Address implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $professional = true;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $companyName = null;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private string $streetAddress = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $restAddress = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Regex(pattern="/^[A-Za-z0-9]{2}\d{3}$/", message="Code postal invalide.")
     */
    private string $zipCode = '';

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    private string $locality = '';

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

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isProfessional(): bool
    {
        return $this->professional;
    }

    public function setProfessional(bool $professional): Address
    {
        $this->professional = $professional;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return u($this->firstName)->upper()->toString();
    }

    public function setFirstName(?string $firstName): Address
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return u($this->lastName)->upper()->toString();
    }

    public function setLastName(?string $lastName): Address
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }

    public function getName(): string
    {
        return $this->companyName !== null ? u($this->companyName)->upper()->toString() : $this->getFullName();
    }

    public function getCompanyName(): ?string
    {
        return u($this->companyName)->upper()->toString();
    }

    public function setCompanyName(?string $companyName): Address
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getStreetAddress(): string
    {
        return u($this->streetAddress)->upper()->toString();
    }

    public function setStreetAddress(string $streetAddress): self
    {
        $this->streetAddress = $streetAddress;
        return $this;
    }

    public function getRestAddress(): ?string
    {
        return u($this->restAddress)->upper()->toString();
    }

    public function setRestAddress(?string $restAddress): self
    {
        $this->restAddress = $restAddress;
        return $this;
    }

    public function getZipCode(): string
    {
        return u($this->zipCode)->upper()->toString();
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getLocality(): string
    {
        return u($this->locality)->upper()->toString();
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

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): Address
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'professional' => $this->isProfessional(),
            'companyName' => $this->companyName,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'streetAddress' => $this->streetAddress,
            'restAddress' => $this->restAddress,
            'locality' => $this->locality,
            'zipCode' => $this->zipCode,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            "%s - %s %s %s",
            $this->getFullName(),
            $this->getStreetAddress(),
            $this->getZipCode(),
            $this->getLocality()
        );
    }
}
