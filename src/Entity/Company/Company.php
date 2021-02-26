<?php

declare(strict_types=1);

namespace App\Entity\Company;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"client"=Client::class, "member"=Member::class, "organization"=Organization::class})
 */
abstract class Company
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column
     */
    protected string $name;

    /**
     * @ORM\Column
     */
    protected string $vatNumber;

    /**
     * @ORM\Column
     */
    protected string $companyNumber;

    abstract public static function getType(): string;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(string $vatNumber): self
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }

    public function getCompanyNumber(): string
    {
        return $this->companyNumber;
    }

    public function setCompanyNumber(string $companyNumber): self
    {
        $this->companyNumber = $companyNumber;
        return $this;
    }
}
