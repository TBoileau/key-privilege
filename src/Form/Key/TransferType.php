<?php

declare(strict_types=1);

namespace App\Form\Key;

use App\Entity\Key\Account;
use App\Entity\Key\Transfer;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class TransferType extends AbstractType
{
    /**
     * @return array<string, mixed>
     */
    abstract protected function getFromOptions(Manager $manager): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function getToOptions(Manager $manager): array;

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var ChoiceGroupView $choiceGroup */
        foreach ($view->children["from"]->vars["choices"] as $choiceGroup) {
            usort(
                $choiceGroup->choices,
                fn (ChoiceView $aChoice, ChoiceView $bChoice): int => $aChoice->label <=> $bChoice->label
            );
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Manager $manager */
        $manager = $options["manager"];

        $accountOptions = [
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
                        "%s - %s - Solde : %d clés",
                        $customer->getClient()->getName(),
                        $customer->getFullName(),
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
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire :',
            ])
            ->add("from", EntityType::class, $accountOptions + $this->getFromOptions($manager) + [
                "label" => "Depuis le compte clés :",
                "class" => Account::class,
            ])
            ->add("to", EntityType::class, $accountOptions + $this->getToOptions($manager) + [
                "label" => "Vers le compte clés :",
                "class" => Account::class,
            ])
            ->add("points", IntegerType::class, [
                "label" => "Montant du transfert :"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("data_class", Transfer::class);
        $resolver->setRequired("manager");
        $resolver->setAllowedTypes("manager", [Manager::class]);
    }
}
