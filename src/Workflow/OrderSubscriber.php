<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Entity\Order\Order;
use App\Entity\User\User;
use App\Repository\Order\OrderRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class OrderSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return ["workflow.order.guard.valid_cart" => "onGuardValidCart"];
    }

    public function onGuardValidCart(GuardEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user->getAccount()->getBalance() < $order->getTotal()) {
            $event->setBlocked(true);
        }
    }
}
