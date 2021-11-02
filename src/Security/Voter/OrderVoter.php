<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Order\Order;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrderVoter extends Voter
{
    public const SHOW = "show";

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::SHOW]) && $subject instanceof Order;
    }

    /**
     * @param Order $order
     */
    protected function voteOnAttribute(string $attribute, $order, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        return $user === $order->getUser() || (
            $user instanceof SalesPerson
            && $order->getUser() instanceof Customer
            && $user->getClients()->contains($order->getUser()->getClient())
        ) || (
            $user instanceof Manager
            && $order->getUser() instanceof Customer
            && $user->getMembers()->contains($order->getUser()->getClient()->getMember())
        );
    }
}
