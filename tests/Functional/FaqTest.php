<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FaqTest extends WebTestCase
{
    public function testIfFaqAndRulesWork(): void
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

        $client->request(Request::METHOD_GET, $urlGenerator->generate("faq"));

        $this->assertResponseIsSuccessful();
    }
}
