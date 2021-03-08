<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("streetAddress", TextType::class, [
                "label" => "Adresse :"
            ])
            ->add("restAddress", TextType::class, [
                "label" => "Complément d'adresse :",
                "required" => false
            ])
            ->add("zipCode", TextType::class, [
                'label' => "Code postal :"
            ])
            ->add("locality", TextType::class, [
                'label' => "Ville :"
            ])
            ->add("phone", TextType::class, [
                'label' => "N° de téléphone :"
            ])
            ->add("email", EmailType::class, [
                'label' => "Adresse email :"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Address::class);
    }
}
