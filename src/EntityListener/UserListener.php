<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\User\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserListener
{
    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function prePersist(User $user): void
    {
        $this->encodePassword($user);
    }

    public function preUpdate(User $user): void
    {
        $this->encodePassword($user);
    }

    private function encodePassword(User $user): void
    {
        if ($user->getPlainPassword() === null) {
            return;
        }

        $user->setPassword(
            $this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword())
        );
    }
}
