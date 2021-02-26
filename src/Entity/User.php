<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class User extends AbstractUser
{
    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     */
    private Client $client;

    public function getRole(): string
    {
        return "ROLE_CLIENT";
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
}
