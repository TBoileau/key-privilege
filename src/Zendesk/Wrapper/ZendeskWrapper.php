<?php

declare(strict_types=1);

namespace App\Zendesk\Wrapper;

use App\Entity\Contact;
use App\Zendesk\Store\TicketStore;
use Zendesk\API\HttpClient;

/**
 * @codeCoverageIgnore
 */
class ZendeskWrapper implements ZendeskWrapperInterface
{
    private HttpClient $client;

    private TicketStore $ticketStore;

    public function __construct(HttpClient $client, TicketStore $ticketStore)
    {
        $this->client = $client;
        $this->ticketStore = $ticketStore;
    }

    public function create(Contact $contact): void
    {
        $this->client->tickets()->create([
            "subject" => $contact->subject,
            "comment" => [
                "body" => $contact->content
            ],
            'requester' => array(
                'name' => $contact->name,
                'email' => $contact->email,
            ),
            "priority" => "normal"
        ]);
        $this->ticketStore->add($contact);
    }
}
