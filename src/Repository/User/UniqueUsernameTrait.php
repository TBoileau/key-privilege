<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User\User;

trait UniqueUsernameTrait
{
    /**
     * @param array<string, mixed> $criteria
     * @return array<User>
     */
    public function findByUniqueUsername(array $criteria): array
    {
        return $this->_em->getRepository(User::class)->findBy($criteria);
    }
}
