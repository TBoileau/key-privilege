<?php

declare(strict_types=1);

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class WalletStateField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): WalletStateField
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath("admin/field/wallet_state.html.twig");
    }
}
