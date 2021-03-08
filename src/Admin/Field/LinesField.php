<?php

declare(strict_types=1);

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class LinesField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): LinesField
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath("admin/field/lines.html.twig");
    }
}
