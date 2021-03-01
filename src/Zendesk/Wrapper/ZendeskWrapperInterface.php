<?php

declare(strict_types=1);

namespace App\Zendesk\Wrapper;

use App\Entity\Contact;

interface ZendeskWrapperInterface
{
    public function create(Contact $contact): void;
}
