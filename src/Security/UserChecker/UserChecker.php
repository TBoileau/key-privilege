<?php

declare(strict_types=1);

namespace App\Security\UserChecker;

use App\Entity\User\User;
use App\Security\Exception\AccountSuspendedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return; // @codeCoverageIgnore
        }

        if ($user->isSuspended()) {
            throw new AccountSuspendedException($user, "Votre compte a été suspendu.");
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
