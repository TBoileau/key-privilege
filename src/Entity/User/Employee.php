<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Company\Member;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
trait Employee
{
    /**
     * @ORM\ManyToOne(targetEntity=Member::class)
     */
    protected ?Member $member = null;

    /**
     * @Assert\NotBlank
     * @Assert\Regex(pattern="/^0[0-9]{9}$/", message="Cette valeur n'est pas un numéro de téléphone valide.")
     * @ORM\Column
     */
    protected string $phone;

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }
}
