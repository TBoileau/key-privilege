<?php

namespace App\Controller\Admin;

use App\Admin\Field\RoleField;
use App\Admin\Field\RulesAgreementField;
use App\Entity\User\Customer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur (client)')
            ->setEntityLabelInPlural('Utilisateurs (client)')
            ->setDefaultSort(['firstName' => 'ASC', 'lastName' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Utilisateur (client)');
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
        yield AssociationField::new('client', 'Client')
            ->setCrudController(ClientCrudController::class);
        yield BooleanField::new('manualDelivery', 'Livraison manuelle');
    }
}
