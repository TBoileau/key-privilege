<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Address;
use App\Entity\Rules;
use App\Entity\RulesAgreement;
use App\Entity\Key\Account;
use App\Repository\User\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Stringable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use function Symfony\Component\String\u;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\EntityListeners({"App\EntityListener\UserListener"})
 * @ORM\Table(name="`user`")
 * @UniqueEntity(
 *     "username",
 *     repositoryMethod="findByUnique",
 *     message="Cet identifiant existe déjà."
 * )
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=true)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "customer"=Customer::class,
 *      "collaborator"=Collaborator::class,
 *      "sales_person"=SalesPerson::class,
 *      "manager"=Manager::class
 * })
 */
abstract class User implements UserInterface, Stringable
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(unique=true)
     * @Gedmo\Slug(fields={"lastName"}, separator="", unique=true, updatable=false)
     */
    protected string $username;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank
     * @Assert\Email
     */
    protected string $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected string $password;

    private ?string $plainPassword = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $forgottenPasswordToken;

    /**
     * @ORM\OneToMany(targetEntity=RulesAgreement::class, mappedBy="user", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\OrderBy({"agreedAt"="desc"})
     * @var Collection<int, RulesAgreement>
     */
    protected Collection $rulesAgreements;

    /**
     * @Assert\NotBlank
     * @ORM\Column
     */
    protected string $firstName = "";

    /**
     * @Assert\NotBlank
     * @ORM\Column
     */
    protected string $lastName = "";

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    protected DateTimeImmutable $registeredAt;

    /**
     * @ORM\Column(type="datetime_immutable",nullable=true)
     */
    protected ?DateTimeImmutable $lastLogin;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $suspended = false;

    /**
     * @ORM\OneToOne(targetEntity=Account::class, cascade={"persist"}, fetch="EAGER", inversedBy="user")
     * @ORM\JoinColumn(nullable=false)
     */
    protected Account $account;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, cascade={"persist"})
     */
    private ?Address $deliveryAddress = null;

    /**
     * @var Collection<array-key, Address>
     * @ORM\ManyToMany(targetEntity=Address::class)
     * @ORM\JoinTable(name="user_delivery_addresses")
     */
    private Collection $deliveryAddresses;

    public function __construct()
    {
        $this->rulesAgreements = new ArrayCollection();
        $this->registeredAt = new DateTimeImmutable();
        $this->account = new Account();
        $this->account->setUser($this);
        $this->deliveryAddresses = new ArrayCollection();
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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @see UserInterface
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles[] = 'ROLE_USER';
        $roles[] = $this->getRole();

        return array_unique($roles);
    }

    abstract public function getRole(): string;

    abstract public function getRoleName(): string;

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
    public function getSalt(): ?string
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
        return u(sprintf("%s %s", $this->firstName, $this->lastName))->upper()->toString();
    }

    public function acceptRules(Rules $rules): void
    {
        $this->agreeRules($rules, true);
    }

    public function refuseRules(Rules $rules): void
    {
        $this->agreeRules($rules, false);
    }

    protected function getAgreementByRules(Rules $rules): ?RulesAgreement
    {
        $criteria = (new Criteria())
            ->setMaxResults(1)
            ->andWhere(Criteria::expr()->eq("rules", $rules));

        $agreement = $this->rulesAgreements->matching($criteria)->first();
        return !$agreement ? null : $agreement;
    }

    protected function agreeRules(Rules $rules, bool $accepted): void
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
        return u($this->firstName)->upper()->toString();
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return u($this->lastName)->upper()->toString();
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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @return Collection<array-key, Address>
     */
    public function getDeliveryAddresses(): Collection
    {
        return $this->deliveryAddresses;
    }
}
