<?php

declare(strict_types=1);

namespace App\Entity\Company;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Organization extends Company
{
    /**
     * @var Collection<int, Member>
     * @ORM\OneToMany(targetEntity=Member::class, mappedBy="organization")
     */
    private Collection $members;

    public static function getType(): string
    {
        return "Groupement";
    }

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }
}
