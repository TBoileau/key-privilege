<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Collaborator extends AbstractUser
{
    use Employee;

    public function getRole(): string
    {
        return "ROLE_COLLABORATOR";
    }
}
