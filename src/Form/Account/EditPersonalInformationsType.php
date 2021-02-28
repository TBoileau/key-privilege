<?php

declare(strict_types=1);

namespace App\Form\Account;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPersonalInformationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("firstName", TextType::class, [
                "label" => "Prénom",
                "empty_data" => ""
            ])
            ->add("lastName", TextType::class, [
                "label" => "Nom",
                "empty_data" => ""
            ])
            ->add("email", EmailType::class, [
                "label" => "Adresse email",
                "empty_data" => ""
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", User::class);
    }
}
