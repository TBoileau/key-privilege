<?php

declare(strict_types=1);

namespace App\Zendesk\Store;

use App\Entity\Contact;

class TicketStore
{
    /**
     * @var array<Contact>
     */
    private array $tickets = [];

    public function add(Contact $contact): void
    {
        $this->tickets[] = $contact;
    }

    /**
     * @return Contact[]
     */
    public function getTickets(): array
    {
        return $this->tickets;
    }
}
