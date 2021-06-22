<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Company\Organization;
use App\Validator\CompanyNumber;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrganizationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Organization::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Groupement')
            ->setEntityLabelInPlural('Groupements')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Groupement');
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
        yield AssociationField::new("members", "Adhérents")
            ->setCrudController(MemberCrudController::class)
            ->setTemplatePath("admin/field/organization_members.html.twig")
            ->onlyOnDetail();
    }
}
