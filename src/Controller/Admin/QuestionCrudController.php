<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Validator\CompanyNumber;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Question')
            ->setEntityLabelInPlural('Questions')
            ->setDefaultSort(['name' => 'ASC']);
    }


    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Question');
        yield TextField::new('name', 'Intitulé de la question')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield TextEditorField::new('answer', 'Réponse')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
    }
}
