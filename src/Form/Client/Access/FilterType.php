<?php

declare(strict_types=1);

namespace App\Form\Client\Access;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add("keywords", TextType::class, [
            "label" => "Recherche",
            "required" => false,
            "attr" => [
                "placeholder" => "Recherche"
            ]
        ]);
    }
}
