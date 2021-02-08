<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Rules;
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
        $user->setForgottenPasswordToken("token");
        $this->assertEquals("token", $user->getForgottenPasswordToken());
        $this->assertNull($user->getSalt());
        $rules = new Rules();
        $user->refuseRules($rules);
        $this->assertFalse($user->hasAcceptedRules($rules));
        $user->acceptRules($rules);
        $this->assertTrue($user->hasAcceptedRules($rules));
        $this->assertEmpty($user->getFullName());
    }
}
