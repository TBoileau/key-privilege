<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ForgottenPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add("email", EmailType::class, [
            "label" => "Adresse email :",
            "constraints" => [
                new Email(),
                new NotBlank()
            ],
            "label_attr" => [
                "class" => "col-12 col-md-5 text-end col-form-label"
            ]
        ]);
    }
}
