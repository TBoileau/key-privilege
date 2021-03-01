<?php

namespace App\Controller\Admin;

use App\Entity\Company\Member;
use App\Validator\CompanyNumber;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\NotBlank;

class MemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Member::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Adhérent')
            ->setEntityLabelInPlural('Adhérents')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Adhérent');
        yield TextField::new('type', 'Typologie')
            ->hideOnForm();
        yield TextField::new('name', 'Raison sociale')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield TextField::new('companyNumber', 'N° de SIRET')
            ->setFormTypeOption("constraints", [
                new NotBlank(),
                new CompanyNumber()
            ]);
        yield TextField::new('vatNumber', 'N° TVA intra.')
            ->hideOnForm();
        yield AssociationField::new('organization', 'Groupement')
            ->setCrudController(OrganizationCrudController::class);
        yield AssociationField::new("clients", "Clients")
            ->setTemplatePath("admin/field/member_clients.html.twig")
            ->onlyOnDetail();
        yield AssociationField::new("managers", "Administrateurs")
            ->setTemplatePath("admin/field/member_managers.html.twig")
            ->onlyOnDetail();
        yield AssociationField::new("salesPersons", "Commerciaux")
            ->setTemplatePath("admin/field/member_sales_persons.html.twig")
            ->onlyOnDetail();
        yield AssociationField::new("collaborators", "Collaborateurs")
            ->setTemplatePath("admin/field/member_collaborators.html.twig")
            ->onlyOnDetail();
    }
}
