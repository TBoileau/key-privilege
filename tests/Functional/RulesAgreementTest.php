<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Rules;
use App\Entity\User;
use App\Repository\RulesRepository;
use App\Repository\UserRepository;
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

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user+refused+rules@email.com"]);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("index"));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_rules");

        $client->submitForm("Accepter");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user+refused+rules@email.com"]);

        /** @var RulesRepository $rulesRepository */
        $rulesRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(Rules::class);

        /** @var Rules $rules */
        $rules = $rulesRepository->findOneBy([]);

        $this->assertTrue($user->hasAcceptedRules($rules));

        $client->followRedirect();

        $this->assertRouteSame("index");
    }

    public function testIfUserRefuseRules(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()
            ->get("doctrine.orm.entity_manager")
            ->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user+refused+rules@email.com"]);

        $client->loginUser($user);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("index"));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_rules");

        $client->submitForm("Refuser");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var User $user */
        $user = $userRepository->findOneBy(["email" => "user+refused+rules@email.com"]);

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
