<?php

declare(strict_types=1);

namespace App\Form\Key;

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
                "label" => "Points :",
                "empty_data" => 0
            ])
            ->add("internReference", TextType::class, [
                "label" => "Référence interne :",
                "required" => false
            ])
            ->add("mode", ChoiceType::class, [
                "label" => "Mode de paiement :",
                "choices" => [
                    Purchase::MODE_BANK_WIRE => Purchase::MODE_BANK_WIRE,
                    Purchase::MODE_CHECK => Purchase::MODE_CHECK
                ]
            ]);

        /** @var Manager $manager */
        $manager = $options["manager"];

        if ($manager->getMembers()->count() > 1) {
            $builder->add("account", EntityType::class, [
                "label" => "Compte clé :",
                "class" => Account::class,
                "choice_name" => fn (Account $account) => $account->getCompany()->getName(),
                "query_builder" => fn (AccountRepository $repository) => $repository
                    ->createQueryBuilderAccountByManager($manager)
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
