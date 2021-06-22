<?php

declare(strict_types=1);

namespace App\Tests\Functional\Member\Access;

use App\Entity\User\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LogAsAccessTest extends WebTestCase
{
    public function testIfLogAsUserIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("home", ["_switch_user" => "user11"])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("home");

        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $client->getContainer()->get("security.token_storage");

        $this->assertEquals("user11", $tokenStorage->getToken()->getUser()->getUsername());

        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $authorizationChecker = $client->getContainer()->get("security.authorization_checker");

        $this->assertTrue($authorizationChecker->isGranted("IS_IMPERSONATOR"));

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("home", ["_switch_user" => "_exit"])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("home");

        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $client->getContainer()->get("security.token_storage");

        $this->assertEquals("user1", $tokenStorage->getToken()->getUser()->getUsername());
    }
}
