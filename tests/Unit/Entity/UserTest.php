<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUser(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
        $user->setEmail("email@email.com");
        $this->assertEquals("email@email.com", $user->getEmail());
        $this->assertEquals("email@email.com", $user->getUsername());
        $user->setRoles(["ROLE_ADMIN"]);
        $this->assertEquals(["ROLE_ADMIN", "ROLE_USER"], $user->getRoles());
        $user->eraseCredentials();
        $user->setPassword("password");
        $this->assertEquals("password", $user->getPassword());
        $this->assertNull($user->getSalt());
    }
}
