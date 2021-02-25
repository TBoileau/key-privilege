<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Client extends Company
{
    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="clients")
     */
    private Member $member;

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;
        return $this;
    }
}
