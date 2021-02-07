<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $email;

    /**
     * @var array<string>
     * @ORM\Column(type="json")
     */
    private array $roles = [];

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
     * @var Collection<int, RulesAgreement>
     */
    private Collection $rulesAgreements;

    public function __construct()
    {
        $this->rulesAgreements = new ArrayCollection();
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
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

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
        return "";
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
}
