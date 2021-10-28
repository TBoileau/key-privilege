<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("professional", ChoiceType::class, [
                "label" => "Adresse professionnelle ?",
                "choices" => [
                    "Oui" => true,
                    "Non" => false,
                ],
                "attr" => [
                    "class" => "address-professional",
                ]
            ])
            ->add("firstName", TextType::class, [
                "label" => "Prénom :",
                "empty_data" => ""
            ])
            ->add("lastName", TextType::class, [
                "label" => "Nom :",
                "empty_data" => ""
            ])
            ->add("companyName", TextType::class, [
                'required' => false,
                "label" => "Raison sociale :",
                "empty_data" => "",
                "row_attr" => [
                    "class" => "address-company-name",
                ]
            ])
            ->add("streetAddress", TextType::class, [
                "label" => "Adresse :",
                "empty_data" => ""
            ])
            ->add("restAddress", TextType::class, [
                "label" => "Complément d'adresse :",
                "required" => false
            ])
            ->add("zipCode", TextType::class, [
                'label' => "Code postal :",
                "empty_data" => ""
            ])
            ->add("locality", TextType::class, [
                'label' => "Ville :",
                "empty_data" => ""
            ])
            ->add("phone", TextType::class, [
                'label' => "N° de téléphone :",
                "empty_data" => ""
            ])
            ->add("email", EmailType::class, [
                'label' => "Adresse email :",
                "empty_data" => ""
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Address::class);
    }
}
