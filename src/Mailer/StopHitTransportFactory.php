<?php

declare(strict_types=1);

namespace App\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class StopHitTransportFactory extends AbstractTransportFactory
{
    public string $mailerSender;

    public function __construct(
        string $mailerSender,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ) {
        $this->mailerSender = $mailerSender;
        parent::__construct($dispatcher, $client, $logger);
    }


    /**
     * @return array<int, string>
     */
    protected function getSupportedSchemes(): array
    {
        return ['spothit+api'];
    }

    public function create(Dsn $dsn): TransportInterface
    {
        return new SpotHitApiTransport(
            $this->getUser($dsn),
            $this->mailerSender,
            $this->client,
            $this->dispatcher,
            $this->logger
        );
    }
}
