<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewAddressType extends AddressType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        if ($options['type'] === null) {
            $builder->add("type", ChoiceType::class, [
                'label' => "Type d'adresse",
                "choices" => [
                    "Facturation" => "billing",
                    "Livraison" => "delivery"
                ],
                "mapped" => false
            ]);
        }
        $builder->add("default", CheckboxType::class, [
            "label" => "Adresse par défaut ?",
            "mapped" => false,
            "required" => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('type', null);
    }
}
