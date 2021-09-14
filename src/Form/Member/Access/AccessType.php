<?php

declare(strict_types=1);

namespace App\Form\Member\Access;

use App\Entity\Company\Client;
use App\Entity\Company\Member;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Employee;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use App\Repository\Company\ClientRepository;
use App\Repository\Company\MemberRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("firstName", TextType::class, [
                "label" => "Prénom :",
                "empty_data" => ""
            ])
            ->add("lastName", TextType::class, [
                "label" => "Nom :",
                "empty_data" => ""
            ])
            ->add("email", EmailType::class, [
                "label" => "Adresse email :",
                "empty_data" => ""
            ])
            ->add("phone", TextType::class, [
                "label" => "N° de téléphone :",
                "empty_data" => ""
            ]);


        /** @var Manager $manager */
        $manager = $options["manager"];

        if ($manager->getMembers()->count() > 1) {
            $builder->add("member", EntityType::class, [
                "label" => "Adhérent :",
                "class" => Member::class,
                "choice_label" => "name",
                "query_builder" => fn (MemberRepository $repository) => $repository->createQueryBuilder("m")
                    ->where("m.id IN (:members)")
                    ->setParameter(
                        "members",
                        $manager->getMembers()->map(fn (Member $member) => $member->getId())->toArray()
                    )
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var Manager|SalesPerson|Collaborator $employee */
            $employee = $event->getData();

            if ($employee instanceof Manager) {
                $event->getForm()->add("isInEmailCopy", ChoiceType::class, [
                    "required" => false,
                    "label" => "Mettre en copie d'email (commandes) ?",
                    "choices" => [
                        "Oui" => 1,
                        "Non" => 0
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired("manager");
        $resolver->setAllowedTypes("manager", [Manager::class]);
        $resolver->setDefault("data_class", User::class);
    }
}
