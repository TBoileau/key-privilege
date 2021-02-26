<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Collaborator extends User
{
    use Employee;

    public function getRole(): string
    {
        return "ROLE_COLLABORATOR";
    }
}
