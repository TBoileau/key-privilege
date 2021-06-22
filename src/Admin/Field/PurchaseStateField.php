<?php

declare(strict_types=1);

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class PurchaseStateField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): PurchaseStateField
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath("admin/field/purchase_state.html.twig");
    }
}
