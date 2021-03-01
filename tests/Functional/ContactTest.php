<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User\User;
use App\Zendesk\DataCollector\TicketCollector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContactTest extends WebTestCase
{
    public function testIfContactFormWorks(): void
    {
        $client = static::createClient();

        $client->enableProfiler();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("contact"));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Envoyer", [
            "contact[name]" => "Jean Dupont",
            "contact[email]" => "email@email.com",
            "contact[subject]" => "subject",
            "contact[content]" => "content"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var TicketCollector $dataCollector */
        $dataCollector = $client->getProfile()->getCollector(TicketCollector::class);

        $this->assertCount(1, $dataCollector->getTickets());

        $client->followRedirect();

        $this->assertSelectorTextContains(
            "div.toast-body",
            "Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais."
        );
    }
}
