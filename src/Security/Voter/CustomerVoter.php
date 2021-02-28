<?php

namespace App\Security\Voter;

use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CustomerVoter extends Voter
{
    public const ROLE_SUSPEND = "suspend";

    public const ROLE_ACTIVE = "active";

    public const ROLE_UPDATE = "update";

    public const ROLE_RESET = "reset";

    public const ROLE_DELETE = "delete";

    public const ROLE_LOG_AS = "log_as";

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
                self::ROLE_SUSPEND,
                self::ROLE_ACTIVE,
                self::ROLE_UPDATE,
                self::ROLE_RESET,
                self::ROLE_DELETE,
                self::ROLE_LOG_AS
            ]) && $subject instanceof Customer;
    }

    /**
     * @param Customer $customer
     */
    protected function voteOnAttribute(string $attribute, $customer, TokenInterface $token): bool
    {
        /** @var SalesPerson|Manager $employee */
        $employee = $token->getUser();

        if ($employee instanceof Manager && !$employee->getMembers()->contains($customer->getClient()->getMember())) {
            return false;
        }

        if ($employee instanceof SalesPerson && $customer->getClient()->getSalesPerson() !== $employee) {
            return false;
        }

        switch ($attribute) {
            case self::ROLE_ACTIVE:
                return $customer->isSuspended();
            case self::ROLE_SUSPEND:
                return !$customer->isSuspended();
        }

        return true;
    }
}
