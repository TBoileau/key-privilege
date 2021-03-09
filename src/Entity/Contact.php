<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Order\Order;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

class Contact implements \Serializable
{
    /**
     * @Assert\NotBlank
     */
    public string $name;

    /**
     * @Assert\NotBlank
     * @Assert\Email
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank
     */
    public string $subject;

    /**
     * @Assert\NotBlank
     */
    public string $content;

    public static function createFromOrder(Order $order): Contact
    {
        $contact = new self();

        $contact->name = $order->getUser()->getFullName();
        $contact->subject = sprintf("Demande de SAV - N° de commande %s", $order->getReference());
        $contact->email = $order->getUser()->getEmail();

        return $contact;
    }

    public function serialize(): string
    {
        return \serialize([
            "name" => $this->name,
            "email" => $this->email,
            "subject" => $this->subject,
            "content" => $this->content
        ]);
    }

    public function unserialize($serialized): void
    {
        [
            "name" => $this->name,
            "email" => $this->email,
            "subject" => $this->subject,
            "content" => $this->content
        ] = \unserialize($serialized);
    }
}
