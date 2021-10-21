<?php

declare(strict_types=1);

namespace App\Entity\Company;

use App\EntityListener\CompanyListener;
use App\Validator\CompanyNumber;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({CompanyListener::class}))
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=true)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"client"=Client::class, "member"=Member::class, "organization"=Organization::class})
 */
abstract class Company implements Stringable
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    protected string $name;

    /**
     * @ORM\Column
     */
    protected string $vatNumber;

    /**
     * @ORM\Column
     * @CompanyNumber
     */
    protected string $companyNumber;

    abstract public static function getType(): string;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return u($this->name)->upper()->toString();
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

    public function __toString(): string
    {
        return $this->name;
    }
}
