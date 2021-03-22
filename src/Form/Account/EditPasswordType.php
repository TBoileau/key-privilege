<?php

declare(strict_types=1);

namespace App\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("currentPassword", PasswordType::class, [
                "label" => "Mot de passe actuel",
                "constraints" => [
                    new NotBlank(),
                    new UserPassword()
                ]
            ])
            ->add("plainPassword", RepeatedType::class, [
                "type" => PasswordType::class,
                "invalid_message" => "Votre mot de passe doit être similaire à la confirmation.",
                "first_options" => [
                    "label" => "Nouveau mot de passe"
                ],
                "second_options" => [
                    "label" => "Confirmez votre mot de passe"
                ],
                "constraints" => [
                    new Length(["min" => 8]),
                    new NotBlank()
                ]
            ]);
    }
}
