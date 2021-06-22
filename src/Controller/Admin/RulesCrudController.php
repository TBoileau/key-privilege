<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Rules;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class RulesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rules::class;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Règlement')
            ->setEntityLabelInPlural('Règlements')
            ->setDateFormat('dd/MM/YYYY')
            ->setDateTimeFormat('dd/MM/YYYY hh:mm')
            ->setDefaultSort(['publishedAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn (Action $action) => $action->displayIf(
                    fn (Rules $rules) => $rules->getPublishedAt() > new \DateTimeImmutable()
                )
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action->displayIf(
                    fn (Rules $rules) => $rules->getPublishedAt() > new \DateTimeImmutable()
                )
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Règlement');
        yield DateTimeField::new('publishedAt', 'Date de publication')
            ->setFormTypeOption("constraints", [
                new NotBlank(),
                new GreaterThan(new \DateTimeImmutable())
            ]);
        yield TextEditorField::new('content', 'Contenu')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
    }
}
