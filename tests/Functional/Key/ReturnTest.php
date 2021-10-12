<?php

declare(strict_types=1);

namespace App\Tests\Functional\Key;

use App\Entity\Company\Member;
use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReturnTest extends WebTestCase
{
    public function testAsManagerIfTransferKeysIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Member $menber */
        $member = $entityManager->find(Member::class, 3);

        $manager->getMembers()->add($member);

        $entityManager->flush();

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("key_return"));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Rétrocéder", [
            "return[points]" => 1000,
            "return[from]" => 6,
            "return[to]" => 1
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Account $account1 */
        $account1 = $entityManager->find(Account::class, 6);

        /** @var Account $account2 */
        $account2 = $entityManager->find(Account::class, 1);

        $this->assertEquals(6000, $account2->getBalance());
        $this->assertEquals(4000, $account1->getBalance());

        $client->followRedirect();

        $this->assertRouteSame("key_index");
    }

    /**
     * @dataProvider provideFailedData
     */
    public function testIfTransferKeysIsFailed(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("key_return"));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Rétrocéder", $formData);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            $errorMessage
        );
    }

    public function provideFailedData(): Generator
    {
        yield [
            [
                "return[points]" => 0,
                "return[from]" => 6,
                "return[to]" => 1
            ],
            "Cette valeur doit être supérieure à 0."
        ];

        yield [
            [
                "return[points]" => 1000,
                "return[from]" => 1,
                "return[to]" => 1
            ],
            "Vous ne pouvez pas transférer des clés entre un seul compte clés."
        ];

        yield [
            [
                "return[points]" => 8000,
                "return[from]" => 6,
                "return[to]" => 1
            ],
            "Le montant de clés ne peut pas être supérieur au solde du compte clés émetteur."
        ];
    }
}
