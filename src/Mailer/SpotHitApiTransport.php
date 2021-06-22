<?php

declare(strict_types=1);

namespace App\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SpotHitApiTransport extends AbstractApiTransport
{
    public string $spotHitApiKey;

    public string $mailerSender;

    public function __construct(
        string $spotHitApiKey,
        string $mailerSender,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        $this->spotHitApiKey = $spotHitApiKey;
        $this->mailerSender = $mailerSender;
        parent::__construct($client, $dispatcher, $logger);
    }

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        return $this->client->request(
            Request::METHOD_POST,
            'https://www.spot-hit.fr/api/envoyer/e-mail',
            [
                "body" => [
                    "key" => $this->spotHitApiKey,
                    "sujet" => $email->getSubject(),
                    "message" => $email->getHtmlBody(),
                    "destinataires" => implode(
                        ",",
                        array_map(fn (Address $address) => $address->getAddress(), $email->getTo())
                    ),
                    "expediteur" => $this->mailerSender,
                    "nom_expediteur" => "Key Privilege"
                ]
            ]
        );
    }

    public function __toString(): string
    {
        return "Spot Hit";
    }
}
