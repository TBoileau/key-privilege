<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User\User;
use App\Entity\Rules;
use App\Repository\RulesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RulesAgreementTest extends WebTestCase
{
    public function testIfUserAcceptRules(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 3);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("home"));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_rules");

        $client->submitForm("Accepter");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, $user->getId());

        /** @var RulesRepository $rulesRepository */
        $rulesRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(Rules::class);

        /** @var Rules $rules */
        $rules = $rulesRepository->findOneBy([]);

        $this->assertTrue($user->hasAcceptedRules($rules));

        $client->followRedirect();

        $this->assertRouteSame("home");
    }

    public function testIfUserRefuseRules(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, 3);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("home"));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_rules");

        $client->submitForm("Refuser");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var User $user */
        $user = $entityManager->find(User::class, $user->getId());

        /** @var RulesRepository $rulesRepository */
        $rulesRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(Rules::class);

        /** @var Rules $rules */
        $rules = $rulesRepository->findOneBy([]);

        $this->assertFalse($user->hasAcceptedRules($rules));

        $client->followRedirect();

        $this->assertRouteSame("security_logout");

        $client->followRedirect();

        $this->assertRouteSame("security_login");
    }
}
