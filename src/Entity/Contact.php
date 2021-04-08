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
