<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Company\Member;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
trait Employee
{
    /**
     * @ORM\ManyToOne(targetEntity=Member::class)
     */
    protected ?Member $member = null;

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }
}
