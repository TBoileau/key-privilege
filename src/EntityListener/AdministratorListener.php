<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Administrator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdministratorListener
{
    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function prePersist(Administrator $administrator): void
    {
        $this->encodePassword($administrator);
    }

    public function preUpdate(Administrator $administrator): void
    {
        $this->encodePassword($administrator);
    }

    private function encodePassword(Administrator $administrator): void
    {
        if ($administrator->getPlainPassword() === null) {
            return;
        }

        $administrator->setPassword(
            $this->userPasswordEncoder->encodePassword($administrator, $administrator->getPlainPassword())
        );
    }
}
