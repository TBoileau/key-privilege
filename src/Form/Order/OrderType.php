<?php

declare(strict_types=1);

namespace App\Form\Order;

use App\Entity\Order\Order;
use App\Entity\User\Customer;
use App\Form\AddressType;
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

            if ($order->getUser() instanceof Customer) {
                /** @var Customer $customer */
                $customer = $order->getUser();

                if ($customer->isManualDelivery()) {
                    $form->add("address", AddressType::class, ["validation_groups" => ["Default", "order"]]);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Order::class);
    }
}
