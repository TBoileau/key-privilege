<?php

declare(strict_types=1);

namespace App\Zendesk\DataCollector;

use App\Entity\Contact;
use App\Zendesk\Store\TicketStore;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketCollector extends AbstractDataCollector
{
    private TicketStore $ticketStore;

    public function __construct(TicketStore $ticketStore)
    {
        $this->ticketStore = $ticketStore;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->data["tickets"] = $this->ticketStore->getTickets();
    }

    /**
     * @return array<Contact>
     */
    public function getTickets(): array
    {
        return $this->data["tickets"] ?? [];
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getTemplate(): ?string
    {
        return 'data_collector/tickets.html.twig';
    }
}
