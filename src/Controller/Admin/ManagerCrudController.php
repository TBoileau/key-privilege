<?php

namespace App\Controller\Admin;

use App\Admin\Field\RoleField;
use App\Admin\Field\RulesAgreementField;
use App\Entity\User\Manager;
use App\Validator\CompanyNumber;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ManagerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Manager::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Administrateur')
            ->setEntityLabelInPlural('Administrateurs')
            ->setDefaultSort(['firstName' => 'ASC', 'lastName' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Administrateur');
        yield RoleField::new('role', 'Rôle')
            ->hideOnForm();
        yield TextField::new('firstName', 'Prénom')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield TextField::new('lastName', 'Nom')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield TextField::new('username', 'Identifiant')->hideOnForm();
        yield EmailField::new('email', 'Adresse email')
            ->setFormTypeOption("constraints", [
                new NotBlank(),
                new Email()
            ]);
        yield TextField::new('phone', 'N° de téléphone')
            ->setFormTypeOption("constraints", [
                new NotBlank(),
                new Regex(["pattern" => "/^0[0-9]{9}$/"])
            ])->hideOnIndex();
        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ])
            ->onlyWhenCreating();
        yield DateTimeField::new('registeredAt', 'Inscription')
            ->hideOnForm();
        yield DateTimeField::new('lastLogin', 'Dernière connexion')
            ->hideOnForm();
        yield RulesAgreementField::new('lastRulesAgreement', 'Règlement')
            ->hideOnForm();
        yield AssociationField::new("rulesAgreements", "Règlement")
            ->setTemplatePath("admin/field/user_rules_agreements.html.twig")
            ->onlyOnDetail();
        yield AssociationField::new('member', 'Adhérent (principal)')
            ->setCrudController(MemberCrudController::class);
        yield AssociationField::new("members", "Adhérents")
            ->setCrudController(MemberCrudController::class)
            ->setTemplatePath("admin/field/manager_members.html.twig")
            ->hideOnIndex();
    }
}
