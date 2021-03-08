<?php

declare(strict_types=1);

namespace App\Tests\Functional\Order;

use App\Entity\Key\Purchase;
use App\Entity\Order\Order;
use App\Entity\Shop\Product;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderTest extends WebTestCase
{
    public function testIfOrderIsSuccessful(): void
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

        $orignalBalance = $manager->getAccount()->getBalance();

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(2, $order->getLines()->first()->getQuantity());

        $client->clickLink("Panier");

        $client->submitForm("Commander");

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->find($order->getId());

        $this->assertEquals("pending", $order->getState());
        $this->assertEquals($manager->getMember()->getAddress(), $order->getAddress());
        $this->assertEquals(
            $orignalBalance - $order->getTotal(),
            $order->getUser()->getAccount()->getBalance()
        );
    }

    public function testAsCustomerIfOrderIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $customer */
        $customer = $entityManager->find(Customer::class, 16);

        $customer->getClient()->setManualDelivery(true);

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
            "order[address][streetAddress]" => "10 rue de la mairie",
            "order[address][locality]" => "Chartres",
            "order[address][zipCode]" => "28000",
            "order[address][email]" => "new+email@email.com",
            "order[address][phone]" => "0213465798"
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->find($order->getId());

        $this->assertEquals("pending", $order->getState());
        $this->assertEquals("10 rue de la mairie", $order->getAddress()->getStreetAddress());
        $this->assertEquals("Chartres", $order->getAddress()->getLocality());
        $this->assertEquals("28000", $order->getAddress()->getZipCode());
        $this->assertEquals("0213465798", $order->getAddress()->getPhone());
        $this->assertEquals("new+email@email.com", $order->getAddress()->getEmail());
        $this->assertEquals(
            $orignalBalance - $order->getTotal(),
            $order->getUser()->getAccount()->getBalance()
        );
    }

    public function testAsCustomerWithoutManualDeliveryIfOrderIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Customer $customer */
        $customer = $entityManager->find(Customer::class, 16);

        $customer->getClient()->setManualDelivery(false);

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
        $this->assertEquals($customer->getClient()->getMember()->getAddress(), $order->getAddress());
    }
}
