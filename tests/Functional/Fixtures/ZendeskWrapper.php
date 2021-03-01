<?php

declare(strict_types=1);

namespace App\Tests\Functional\Fixtures;

use App\Entity\Contact;
use App\Zendesk\Store\TicketStore;
use App\Zendesk\Wrapper\ZendeskWrapperInterface;

class ZendeskWrapper implements ZendeskWrapperInterface
{
    private TicketStore $ticketStore;

    public function __construct(TicketStore $ticketStore)
    {
        $this->ticketStore = $ticketStore;
    }

    public function create(Contact $contact): void
    {
        $this->ticketStore->add($contact);
    }
}
