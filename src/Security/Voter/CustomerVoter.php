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

        if (!$this->voteByRoles($customer, $employee)) {
            return false;
        }

        if (in_array($attribute, [self::ROLE_RESET, self::ROLE_LOG_AS, self::ROLE_UPDATE])) {
            return true;
        }

        if ($attribute === self::ROLE_ACTIVE) {
            return $this->voteOnActive($customer, $employee);
        }

        if ($attribute === self::ROLE_SUSPEND) {
            return $this->voteOnSuspend($customer, $employee);
        }

        return $employee instanceof Manager;
    }

    private function voteByRoles(Customer $customer, SalesPerson | Manager $employee): bool
    {
        if ($employee instanceof Manager) {
            return $employee->getMembers()->contains($customer->getClient()->getMember());
        }

        return $customer->getClient()->getSalesPerson() === $employee;
    }

    private function voteOnActive(Customer $customer, SalesPerson | Manager $employee): bool
    {
        return $customer->isSuspended() && $employee instanceof Manager;
    }

    private function voteOnSuspend(Customer $customer, SalesPerson | Manager $employee): bool
    {
        return !$customer->isSuspended() && $employee instanceof Manager;
    }
}
