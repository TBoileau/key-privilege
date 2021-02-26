<?php

declare(strict_types=1);

namespace App\Form\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class RulesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("refuse", SubmitType::class, [
                "label" => "Refuser",
                "attr" => [
                    "class" => 'btn btn-danger'
                ]
            ])
            ->add("accept", SubmitType::class, [
                "label" => "Accepter",
                "attr" => [
                    "class" => 'btn btn-success'
                ]
            ]);
    }
}
