<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Administrator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdministratorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Administrator::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Administrateur')
            ->setEntityLabelInPlural('Administrateurs')
            ->setDefaultSort(['firstName' => 'ASC', 'lastName' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Administrateur');
        yield TextField::new('firstName', 'Prénom')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield TextField::new('lastName', 'Nom')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield EmailField::new('email', 'Email')
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
    }
}
