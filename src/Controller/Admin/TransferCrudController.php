<?php

namespace App\Controller\Admin;

use App\Admin\Field\TransactionsField;
use App\Entity\Key\Account;
use App\Entity\Key\Transfer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class TransferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Transfer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Transfert')
            ->setEntityLabelInPlural('Transferts')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setDateFormat('dd/MM/YYYY')
            ->setDateTimeFormat('dd/MM/YYYY HH:mm:ss');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new("from", "Émetteur"))
            ->add(EntityFilter::new("to", "Destinataire"))
            ->add(DateTimeFilter::new("createdAt", "Date"));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new("from", "Émetteur")
            ->setFormTypeOption("choice_label", fn (Account $account) => sprintf(
                "%s - Solde : %d clés",
                $account->__toString(),
                $account->getBalance()
            ))
            ->setCrudController(AccountCrudController::class);
        yield AssociationField::new("to", "Destinataire")
            ->setFormTypeOption("choice_label", fn (Account $account) => sprintf(
                "%s - Solde : %d clés",
                $account->__toString(),
                $account->getBalance()
            ))
            ->setCrudController(AccountCrudController::class);
        yield DateTimeField::new('createdAt', 'Date')->hideOnForm();
        yield IntegerField::new('points', 'Points');
        yield TransactionsField::new("transactions", "Transactions")->onlyOnDetail();
    }
}
