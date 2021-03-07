<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\TransactionsField;
use App\Admin\Field\WalletStateField;
use App\Entity\Key\Wallet;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class WalletCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Wallet::class;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->addSelect("purchase")
            ->addSelect("account")
            ->join("entity.purchase", "purchase")
            ->join("entity.account", "account")
            ->where("purchase.state = 'accepted'");
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Portefeuille')
            ->setEntityLabelInPlural('Portefeuilles')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setDateFormat('dd/MM/YYYY')
            ->setDateTimeFormat('dd/MM/YYYY HH:mm:ss');
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
        return $filters
            ->add(EntityFilter::new("account", "Compte point"))
            ->add(DateTimeFilter::new("createdAt", "Date de création"))
            ->add(DateTimeFilter::new("expiredAt", "Date d'expiration"));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Référence')->hideOnForm();
        yield AssociationField::new("account", "Compte point")
            ->setCrudController(AccountCrudController::class);
        yield AssociationField::new("purchase", "Transaction initiale")
            ->setCrudController(TransactionCrudController::class);
        yield DateTimeField::new('createdAt', 'Date de création');
        yield DateTimeField::new('expiredAt', 'Date d\'expiration');
        yield IntegerField::new('balance', 'Solde');
        yield WalletStateField::new('expired', 'Statut')->hideOnForm();
        yield TransactionsField::new("transactions", "Transactions")->onlyOnDetail();
    }
}
