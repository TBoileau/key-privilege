<?php

declare(strict_types=1);

namespace App\Form\Key;

use App\Entity\Address;
use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use App\Entity\User\Manager;
use App\Repository\Key\AccountRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("points", IntegerType::class, [
                "label" => "Montant de votre achat :",
                "empty_data" => 0,
                "help" => "Rappel : 1 étoile = 1 euro HT"
            ])
            ->add("internReference", TextType::class, [
                "label" => "Votre référence interne (visible sur la facture) :",
                "required" => false
            ])
            ->add("mode", ChoiceType::class, [
                "label" => "Mode de paiement :",
                "expanded" => true,
                "choices" => [
                    Purchase::MODE_BANK_WIRE => Purchase::MODE_BANK_WIRE,
                    Purchase::MODE_CHECK => Purchase::MODE_CHECK
                ]
            ]);

        /** @var Manager $manager */
        $manager = $options["manager"];

        $builder->add("deliveryAddress", EntityType::class, [
            'label' => 'Adresse de livraison',
            "class" => Address::class,
            "choice_label" => fn (Address $address) => sprintf(
                "%s - %s %s %s",
                $address->getFullName(),
                $address->getStreetAddress(),
                $address->getLocality(),
                $address->getLocality()
            ),
            "choices" => $manager->getDeliveryAddresses()
                ->filter(fn (Address $address) => ! $address->isDeleted())
        ]);

        $builder->add("billingAddress", EntityType::class, [
            'label' => 'Adresse de facturation',
            "class" => Address::class,
            "choice_label" => fn (Address $address) => sprintf(
                "%s - %s %s %s",
                $address->getFullName(),
                $address->getStreetAddress(),
                $address->getLocality(),
                $address->getLocality()
            ),
            "choices" => $manager->getBillingAddresses()
                ->filter(fn (Address $address) => ! $address->isDeleted())
        ]);

        if ($manager->getMembers()->count() > 1) {
            $builder->add("account", EntityType::class, [
                "label" => "Compte clé :",
                "class" => Account::class,
                "choice_name" => fn (Account $account) => $account->getMember()->getName(),
                "query_builder" => fn (AccountRepository $repository) => $repository
                    ->createQueryBuilderAccountByManagerForPurchase($manager)
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Purchase::class);
        $resolver->setDefault("validation_groups", ["new"]);
        $resolver->setRequired("manager");
        $resolver->setAllowedTypes("manager", [Manager::class]);
    }
}
