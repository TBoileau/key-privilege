<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginTest extends WebTestCase
{
    public function testIfLoginIsSuccessful(): void
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/login");

        $form = $crawler->filter("form[name=login]")->form([
            "email" => "user@email.com",
            "password" => "password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("index");
    }

    public function testIfEmailDoesNotExist(): void
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/login");

        $form = $crawler->filter("form[name=login]")->form([
            "email" => "fail@email.com",
            "password" => "password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");
    }

    public function testIfPasswordIsWrong(): void
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/login");

        $form = $crawler->filter("form[name=login]")->form([
            "email" => "user@email.com",
            "password" => "fail"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");
    }

    public function testIfCsrfIsWrong(): void
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/login");

        $form = $crawler->filter("form[name=login]")->form([
            "email" => "user@email.com",
            "password" => "password",
            "_csrf_token" => "fail"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame("security_login");
    }
}
