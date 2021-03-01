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

    public function prePersist(User $administrator): void
    {
        $this->encodePassword($administrator);
    }

    public function preUpdate(User $administrator): void
    {
        $this->encodePassword($administrator);
    }

    private function encodePassword(User $administrator): void
    {
        if ($administrator->getPlainPassword() === null) {
            return;
        }

        $administrator->setPassword(
            $this->userPasswordEncoder->encodePassword($administrator, $administrator->getPlainPassword())
        );
    }
}
