<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Repository\User\UserRepository;
use App\Entity\Company\Member;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class Manager extends User
{
    use Employee;

    /**
     * @var Collection<int, Member>
     * @ORM\ManyToMany(targetEntity=Member::class, inversedBy="managers")
     * @ORM\JoinTable(name="manager_members")
     */
    private Collection $members;

    public function __construct()
    {
        parent::__construct();
        $this->members = new ArrayCollection();
    }

    public function getRoleName(): string
    {
        return "Administrateur";
    }

    public function getRole(): string
    {
        return "ROLE_MANAGER";
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }
}
