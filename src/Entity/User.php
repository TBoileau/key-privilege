<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=true)
 */
class User implements UserInterface
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank
     * @Assert\Email
     */
    private string $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $forgottenPasswordToken;

    /**
     * @ORM\OneToMany(targetEntity=RulesAgreement::class, mappedBy="user", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\OrderBy({"agreedAt"="desc"})
     * @var Collection<int, RulesAgreement>
     */
    private Collection $rulesAgreements;

    /**
     * @Assert\NotBlank
     * @ORM\Column
     */
    private string $firstName = "";

    /**
     * @Assert\NotBlank
     * @ORM\Column
     */
    private string $lastName = "";

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $registeredAt;

    /**
     * @ORM\Column(type="datetime_immutable",nullable=true)
     */
    private ?DateTimeImmutable $lastLogin;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $suspended = false;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    private Company $company;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    private Role $role;

    /**
     * @var Collection<int, Company>
     * @ORM\ManyToMany(targetEntity=Company::class)
     * @ORM\JoinTable(name="user_companies")
     */
    private Collection $companies;

    public function __construct()
    {
        $this->rulesAgreements = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->registeredAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->role->getRoles();
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): string | null
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    public function getForgottenPasswordToken(): ?string
    {
        return $this->forgottenPasswordToken;
    }

    public function setForgottenPasswordToken(?string $forgottenPasswordToken): self
    {
        $this->forgottenPasswordToken = $forgottenPasswordToken;
        return $this;
    }

    public function getFullName(): string
    {
        return sprintf("%s %s", $this->firstName, $this->lastName);
    }

    public function acceptRules(Rules $rules): void
    {
        $this->agreeRules($rules, true);
    }

    public function refuseRules(Rules $rules): void
    {
        $this->agreeRules($rules, false);
    }

    private function getAgreementByRules(Rules $rules): ?RulesAgreement
    {
        $criteria = (new Criteria())
            ->setMaxResults(1)
            ->andWhere(Criteria::expr()->eq("rules", $rules));

        $agreement = $this->rulesAgreements->matching($criteria)->first();
        return !$agreement ? null : $agreement;
    }

    private function agreeRules(Rules $rules, bool $accepted): void
    {
        $agreement = $this->getAgreementByRules($rules);

        if ($agreement === null) {
            $agreement = (new RulesAgreement())
                ->setUser($this)
                ->setRules($rules);
            $this->rulesAgreements->add($agreement);
        }

        $agreement->setAccepted($accepted)->setAgreedAt(new DateTimeImmutable());
    }

    public function hasAcceptedRules(Rules $rules): bool
    {
        $agreement = $this->getAgreementByRules($rules);

        return $agreement === null ? false : $agreement->isAccepted();
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return Collection<int, RulesAgreement>
     */
    public function getRulesAgreements(): Collection
    {
        return $this->rulesAgreements;
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function getLastLogin(): ?DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTimeImmutable $lastLogin): self
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function setSuspended(bool $suspended): self
    {
        $this->suspended = $suspended;
        return $this;
    }

    public function getLastRulesAgreement(): ?RulesAgreement
    {
        if ($this->rulesAgreements->count() === 0) {
            return null;
        }

        /** @var RulesAgreement $rulesAgreement */
        $rulesAgreement = $this->rulesAgreements->first();

        return $rulesAgreement;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;

        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }
}
