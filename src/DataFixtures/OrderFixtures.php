<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order\Line;
use App\Entity\Order\Order;
use App\Entity\Shop\Product;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Workflow\WorkflowInterface;
use Twig\Token;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    private WorkflowInterface $orderStateMachine;

    private TokenStorageInterface $tokenStorage;

    public function __construct(WorkflowInterface $orderStateMachine, TokenStorageInterface $tokenStorage)
    {
        $this->orderStateMachine = $orderStateMachine;
        $this->tokenStorage = $tokenStorage;
    }

    public function getDependencies(): array
    {
        return [CustomerFixtures::class, ShopFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<User> $users */
        $users = $manager->getRepository(User::class)->findAll();

        /** @var Product $product */
        $product = $manager->getRepository(Product::class)->findOneByAmount(2000);

        /**
         * @var int $k
         * @var User $user
         */
        foreach ($users as $k => $user) {
            if ($user instanceof Customer) {
                $address = $user->getClient()->getMember()->getAddress();
            } else {
                /** @var SalesPerson|Collaborator|Manager $user */
                $address = $user->getMember()->getAddress();
            }

            $token = new UsernamePasswordToken($user, [], "main", $user->getRoles());

            $this->tokenStorage->setToken($token);

            $order = (new Order())
                ->setAddress($address)
                ->setUser($user);

            $order->getLines()->add((new Line())->increaseQuantity()->setOrder($order)->setProduct($product));

            $manager->persist($order);

            $this->orderStateMachine->apply($order, "valid_cart");
        }
        $manager->flush();
    }
}
