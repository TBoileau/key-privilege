<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Key\Transfer;
use App\UseCase\TransferPointsInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransferSubscriber implements EventSubscriberInterface
{
    private TransferPointsInterface $transferPoint;

    public function __construct(TransferPointsInterface $transferPoint)
    {
        $this->transferPoint = $transferPoint;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [BeforeEntityPersistedEvent::class => ['prePersist']];
    }

    public function prePersist(BeforeEntityPersistedEvent $event): void
    {
        if (!$event->getEntityInstance() instanceof Transfer) {
            return;
        }

        /** @var Transfer $transfer */
        $transfer = $event->getEntityInstance();

        $this->transferPoint->execute($transfer);
    }
}
