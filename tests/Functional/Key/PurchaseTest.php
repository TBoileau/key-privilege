<?php

declare(strict_types=1);

namespace App\Tests\Functional\Key;

use App\Entity\Company\Member;
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

class PurchaseTest extends WebTestCase
{
    public function testAsManagerIfAccessAddIsSuccessful(): void
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

        $client->request(Request::METHOD_GET, $urlGenerator->generate("key_purchase"));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Acheter", [
            "purchase[points]" => 1000,
            "purchase[mode]" => Purchase::MODE_CHECK,
            "purchase[internReference]" => "ref",
            "purchase[account]" => 3
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Purchase $purchase */
        $purchase = $entityManager->getRepository(Purchase::class)->findBy([], ["id" => "desc"], 1)[0];

        $this->assertEquals(1000, $purchase->getPoints());
        $this->assertEquals("ref", $purchase->getInternReference());
        $this->assertEquals("pending", $purchase->getState());
        $this->assertEquals(Purchase::MODE_CHECK, $purchase->getMode());
        $this->assertEquals(3, $purchase->getAccount()->getId());
        $this->assertEquals(3, $purchase->getAccount()->getCompany()->getId());
        $this->assertEmailCount(1);

        $client->followRedirect();

        $this->assertRouteSame("key_index");
    }

    /**
     * @dataProvider provideFailedData
     */
    public function testIfPurchaseIsFailed(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("key_purchase"));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Acheter", $formData);

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
                "purchase[points]" => 0,
                "purchase[mode]" => Purchase::MODE_CHECK,
                "purchase[internReference]" => "internReference"
            ],
            "Cette valeur doit être supérieure à 0."
        ];

        yield [
            [
                "purchase[points]" => "",
                "purchase[mode]" => Purchase::MODE_CHECK,
                "purchase[internReference]" => "internReference"
            ],
            "Cette valeur n'est pas valide."
        ];
    }
}
