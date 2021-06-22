<?php

declare(strict_types=1);

namespace App\Tests\Functional\Sav;

use App\Entity\Key\Purchase;
use App\Entity\Order\Order;
use App\Entity\Shop\Product;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Zendesk\DataCollector\TicketCollector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SavTest extends WebTestCase
{
    public function testAsManagerIfTriggeringSavIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneByUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->loginUser($manager);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("sav_index"));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->enableProfiler();

        $crawler = $client->clickLink("Demande de SAV");

        $client->request(
            Request::METHOD_POST,
            '/sav/' . $order->getId() . '/declencher',
            [
                'sav' => [
                    "_token" => $crawler->filter("form[name=sav]")->form()->get("sav")["_token"]->getValue(),
                    "line" => $order->getLines()->first()->getId(),
                    "description" => "Description",
                    "comment" => "Commentaire",
                    'attachments' => ["uploads/image.png"]
                ]
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->assertEmailCount(1);
    }

    public function testIfUploadWorks(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        copy(
            __DIR__ . '/../../../public/uploads/image.png',
            __DIR__ . '/../../../public/uploads/image-test.png'
        );

        $client->request(
            Request::METHOD_POST,
            "/sav/upload",
            [],
            [
                "file" => new UploadedFile(
                    __DIR__ . '/../../../public/uploads/image-test.png',
                    'image.png',
                    'image/png',
                    null,
                    true
                )
            ]
        );

        $this->assertResponseIsSuccessful();
    }
}
