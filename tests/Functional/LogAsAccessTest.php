<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LogAsAccessTest extends WebTestCase
{
    public function testIfLogAsIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user@email.com"]);

        $client->loginUser($user);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("index", ["_switch_user" => "user+1@email.com"])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("index");

        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $client->getContainer()->get("security.token_storage");

        $this->assertEquals("user+1@email.com", $tokenStorage->getToken()->getUser()->getUsername());

        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $authorizationChecker = $client->getContainer()->get("security.authorization_checker");

        $this->assertTrue($authorizationChecker->isGranted("IS_IMPERSONATOR"));

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("index", ["_switch_user" => "_exit"])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("index");

        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $client->getContainer()->get("security.token_storage");

        $this->assertEquals("user@email.com", $tokenStorage->getToken()->getUser()->getUsername());
    }
}
