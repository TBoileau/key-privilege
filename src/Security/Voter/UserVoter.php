<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const ROLE_SUSPEND = "suspend";

    public const ROLE_ACTIVE = "active";

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ROLE_SUSPEND, self::ROLE_ACTIVE]) && $subject instanceof User;
    }

    /**
     * @param User $user
     */
    protected function voteOnAttribute(string $attribute, $user, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ROLE_ACTIVE:
                return $user->isSuspended();
            case self::ROLE_SUSPEND:
                return !$user->isSuspended();
        }

        return false;
    }
}
