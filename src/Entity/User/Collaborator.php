<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class Collaborator extends User
{
    use Employee;

    public function getRole(): string
    {
        return "ROLE_COLLABORATOR";
    }
}
