<?php

declare(strict_types=1);

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class AccountSuspendedException extends AccountStatusException
{
    public function __construct(UserInterface $user, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setUser($user);
    }

    public function getMessageKey(): string
    {
        return $this->message;
    }
}
