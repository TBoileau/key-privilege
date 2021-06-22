<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\LinesField;
use App\Admin\Field\OrderStateField;
use App\Admin\Field\OwnerField;
use App\Admin\Field\TransactionsField;
use App\Admin\Field\WalletsField;
use App\Entity\Order\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compte points')
            ->setEntityLabelInPlural('Comptes points')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setDateFormat('dd/MM/YYYY')
            ->setDateTimeFormat('dd/MM/YYYY HH:mm');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(DateTimeFilter::new("createdAt", "Date de création"));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Refernence');
        yield AssociationField::new('user', 'Commanditaire')
            ->setCrudController(UserCrudController::class);
        yield DateTimeField::new('createdAt', 'Date de commande');
        yield IntegerField::new("total", "Total (points)");
        yield OrderStateField::new('state', 'State');
        yield LinesField::new("lines", "Lignes de commande")->onlyOnDetail();
        yield TransactionsField::new("transactions", "Transactions")->onlyOnDetail();
    }
}
