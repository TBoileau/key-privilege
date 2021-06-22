<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("name", TextType::class, [
                "label" => "Votre nom :",
                "row_attr" => [
                    "class" => "mb-3"
                ]
            ])
            ->add("email", EmailType::class, [
                "label" => "Votre adresse email :",
                "row_attr" => [
                    "class" => "mb-3"
                ]
            ])
            ->add("subject", TextType::class, [
                "label" => "Objet :",
                "row_attr" => [
                    "class" => "mb-3"
                ]
            ])
            ->add("content", TextareaType::class, [
                "label" => "Votre message :"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Contact::class);
    }
}
