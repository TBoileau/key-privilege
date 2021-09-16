<?php

declare(strict_types=1);

namespace App\Tests\Functional\Order;

use App\Entity\Key\Purchase;
use App\Entity\Order\Order;
use App\Entity\Shop\Product;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Pdf\Generator as PdfGenerator;
use App\Pdf\OrderGenerator;
use App\Zendesk\DataCollector\TicketCollector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderTest extends WebTestCase
{
    public function testGeneratePdf(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->find(Order::class, 1);

        /** @var PdfGenerator $generator */
        $generator = $client->getContainer()->get(OrderGenerator::class);

        $generator->generate('test', 'ui/order/pdf.html.twig', ['order' => $order]);

        $this->assertFileExists(__DIR__ . '/../../../public/pdf/test.pdf');
    }

    public function testAsSalesPersonIfListingOrdersIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var SalesPerson $salesPerson */
        $salesPerson = $entityManager->find(SalesPerson::class, 7);

        $client->loginUser($salesPerson);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("order_index"));

        $this->assertResponseIsSuccessful();
    }

    public function testAsManagerIfPassingOrderIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var Product $product */
        $product = $entityManager->find(Product::class, 2000);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("shop_product", [
            "slug" => $product->getSlug()
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $this->assertCount(1, $order->getLines());

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $originalBalance = $manager->getAccount()->getBalance();

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(2, $order->getLines()->first()->getQuantity());

        $client->clickLink("Panier");

        $client->submitForm("Commander", [
            'order[deliveryAddress]' => $manager->getDeliveryAddress()->getId()
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->find($order->getId());

        $this->assertEquals("pending", $order->getState());
        $this->assertEquals(
            $originalBalance - $order->getTotal(),
            $order->getUser()->getAccount()->getBalance()
        );

        $client->followRedirect();

        $client->clickLink("Détail");

        $this->assertResponseIsSuccessful();

        $this->assertFileExists(sprintf(__DIR__ . '/../../../public/pdf/%s.pdf', $order->getReference()));
    }

    public function testAsCustomerIfPassingOrderIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $customer */
        $customer = $entityManager->find(Customer::class, 16);

        $customer->setManualDelivery(true);

        $entityManager->flush();

        $client->loginUser($customer);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var Product $product */
        $product = $entityManager->find(Product::class, 2000);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("shop_product", [
            "slug" => $product->getSlug()
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $customer
        ]);

        $this->assertCount(1, $order->getLines());

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $customer
        ]);

        $orignalBalance = $customer->getAccount()->getBalance();

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(2, $order->getLines()->first()->getQuantity());

        $client->clickLink("Panier");

        $client->submitForm("Commander", [
            'order[deliveryAddress]' => $customer->getDeliveryAddress()->getId()
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->find($order->getId());

        $this->assertEquals("pending", $order->getState());
        $this->assertEquals(
            $orignalBalance - $order->getTotal(),
            $order->getUser()->getAccount()->getBalance()
        );

        $client->followRedirect();

        $client->clickLink("Détail");

        $this->assertResponseIsSuccessful();
    }

    public function testAsCustomerWithoutManualDeliveryIfPassingOrderIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $customer */
        $customer = $entityManager->find(Customer::class, 16);

        $customer->setManualDelivery(false);

        $purchase = (new Purchase())
            ->setAccount($customer->getAccount())
            ->setPoints(2000)
            ->setMode(Purchase::MODE_CHECK)
            ->setState("accepted")
            ->prepare();

        $purchase->getWallet()->addTransaction($purchase);

        $entityManager->persist($purchase);

        $entityManager->flush();

        $client->loginUser($customer);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var Product $product */
        $product = $entityManager->find(Product::class, 2000);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("shop_product", [
            "slug" => $product->getSlug()
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $customer
        ]);

        $this->assertCount(1, $order->getLines());

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $customer
        ]);

        $orignalBalance = $customer->getAccount()->getBalance();

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(2, $order->getLines()->first()->getQuantity());

        $client->clickLink("Panier");

        $client->clickLink("Plus");

        $client->followRedirect();

        $client->submitForm("Commander");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->find($order->getId());

        $this->assertEquals("pending", $order->getState());
        $this->assertEquals(1000, $order->getUser()->getAccount()->getBalance());
        $this->assertEquals(
            $orignalBalance - $order->getTotal(),
            $order->getUser()->getAccount()->getBalance()
        );

        $client->followRedirect();

        $client->clickLink("Détail");

        $this->assertResponseIsSuccessful();

        $client->enableProfiler();

        $crawler = $client->clickLink("Déclencher une demande de SAV");

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
}
