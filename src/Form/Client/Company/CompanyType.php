<?php

declare(strict_types=1);

namespace App\Form\Client\Company;

use App\Entity\Company\Client;
use App\Entity\Company\Member;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Form\AddressType;
use App\Repository\Company\MemberRepository;
use App\Repository\User\SalesPersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("name", TextType::class, [
                "label" => "Raison sociale :",
                "empty_data" => ""
            ])
            ->add("address", AddressType::class, [
                "label" => false
            ])
            ->add("companyNumber", TextType::class, [
                "label" => "N° de SIRET :",
                "empty_data" => ""
            ]);

        $builder->get("address")->remove("email");
        $builder->get("address")->remove("phone");
        $builder->get("address")->remove("firstName");
        $builder->get("address")->remove("lastName");
        $builder->get("address")->remove("professional");
        $builder->get("address")->remove("companyName");

        /** @var Manager $employee */
        $employee = $options["employee"];

        $memberOptions = [];

        if ($employee->getMembers()->count() > 1) {
            $memberOptions = ["group_by" => fn (SalesPerson $salesPerson) => $salesPerson->getMember()->getName()];
        }

        $builder->add("salesPerson", EntityType::class, $memberOptions + [
            "required" => false,
            "label" => "Commercial(e) :",
            "placeholder" => "Non renseigné",
            "class" => SalesPerson::class,
            "choice_label" => fn (SalesPerson $salesPerson) => $salesPerson->getFullName(),
            "query_builder" => fn (SalesPersonRepository $repository) => $repository
                ->createQueryBuilderSalesPersonsByManager($employee)
        ]);

        if ($employee->getMembers()->count() > 1) {
            $builder->add("member", EntityType::class, [
                "label" => "Adhérent :",
                "class" => Member::class,
                "choice_label" => "name",
                "query_builder" => fn (MemberRepository $repository) => $repository
                    ->createQueryBuilderMembersByManager($employee)
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired("employee");
        $resolver->setAllowedTypes("employee", [Manager::class]);
        $resolver->setDefault("data_class", Client::class);
    }
}
