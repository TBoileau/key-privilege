<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Key\Purchase;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurchaseSubscriber implements EventSubscriberInterface
{
    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [BeforeEntityPersistedEvent::class => ['prePersist']];
    }

    public function prePersist(BeforeEntityPersistedEvent $event): void
    {
        if (!$event->getEntityInstance() instanceof Purchase) {
            return;
        }

        /** @var Purchase $purchase */
        $purchase = $event->getEntityInstance();

        $purchase->prepare();
    }
}
