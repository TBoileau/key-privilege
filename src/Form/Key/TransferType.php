<?php

declare(strict_types=1);

namespace App\Form\Key;

use App\Entity\Key\Account;
use App\Entity\Key\Transfer;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Repository\Key\AccountRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Manager $manager */
        $manager = $options["manager"];

        $accountOptions = [
            "query_builder" => fn (AccountRepository $repository) => $repository
                ->createQueryBuilderAccountByManagerForTransfer($manager),
            "choice_label" => function (Account $account) {
                if ($account->getUser() === null) {
                    return sprintf(
                        "%s - Solde : %d clés",
                        $account->getMember()->getName(),
                        $account->getBalance()
                    );
                }

                if ($account->getUser() instanceof Customer) {
                    /** @var Customer $customer */
                    $customer = $account->getUser();

                    return sprintf(
                        "%s - Solde : %d clés",
                        $customer->getClient()->getName(),
                        $account->getBalance()
                    );
                }

                /** @var SalesPerson|Manager|Collaborator $employee */
                $employee = $account->getUser();

                return sprintf(
                    "%s - %s - Solde : %d clés",
                    $employee->getRoleName(),
                    $employee->getFullName(),
                    $account->getBalance()
                );
            },
            "group_by" => function (Account $account) {
                if ($account->getUser() === null) {
                    return "Adhérent";
                }

                if ($account->getUser() instanceof Customer) {
                    return "Client";
                }

                /** @var SalesPerson|Manager|Collaborator $employee */
                $employee = $account->getUser();

                return $employee->getMember()->getName();
            }
        ];

        $builder
            ->add("from", EntityType::class, $accountOptions + [
                "label" => "Depuis le compte clés :",
                "class" => Account::class
            ])
            ->add("to", EntityType::class, $accountOptions + [
                "label" => "Vers le compte clés :",
                "class" => Account::class
            ])
            ->add("points", IntegerType::class, [
                "label" => "Points :"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Transfer::class);
        $resolver->setRequired("manager");
        $resolver->setAllowedTypes("manager", [Manager::class]);
    }
}
