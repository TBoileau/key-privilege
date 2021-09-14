<?php

declare(strict_types=1);

namespace App\Form\Order;

use App\Entity\Address;
use App\Entity\Order\Order;
use App\Entity\User\Customer;
use App\Form\AddressType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Order $order */
            $order = $event->getData();

            $form = $event->getForm();

            if (
                !$order->getUser() instanceof Customer
                || ($order->getUser() instanceof Customer && $order->getUser()->isManualDelivery())
            ) {
                $form->add("deliveryAddress", EntityType::class, [
                    'label' => 'Adresse de livraison',
                    "class" => Address::class,
                    "choice_label" => fn (Address $address) => sprintf(
                        "%s - %s %s %s",
                        $address->getFullName(),
                        $address->getStreetAddress(),
                        $address->getLocality(),
                        $address->getLocality()
                    ),
                    "choices" => $order->getUser()->getDeliveryAddresses()
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Order::class);
    }
}
