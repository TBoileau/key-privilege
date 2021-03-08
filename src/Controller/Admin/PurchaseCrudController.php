<?php

namespace App\Controller\Admin;

use App\Admin\Field\PurchaseStateField;
use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class PurchaseCrudController extends AbstractCrudController
{
    private WorkflowInterface $purchaseStateMachine;

    public function __construct(WorkflowInterface $purchaseStateMachine)
    {
        $this->purchaseStateMachine = $purchaseStateMachine;
    }

    public static function getEntityFqcn(): string
    {
        return Purchase::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setFormOptions([
                "validation_groups" => ["new"]
            ])
            ->setEntityLabelInSingular('Achat de points')
            ->setEntityLabelInPlural('Achats de points')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setDateFormat('dd/MM/YYYY')
            ->setDateTimeFormat('dd/MM/YYYY HH:mm:ss');
    }

    public function configureActions(Actions $actions): Actions
    {
        $purchaseStateMachine = $this->purchaseStateMachine;
        $actionCallback = function (Action $action) use ($purchaseStateMachine) {
            return $action->displayIf(
                fn (Purchase $purchase) => $purchaseStateMachine->getMarking($purchase)->has("pending")
            );
        };

        $cancel = Action::new("cancel", "Annuler")
            ->addCssClass("text-warning")
            ->displayIf(fn (Purchase $purchase) => $purchaseStateMachine->can($purchase, "cancel"))
            ->displayAsLink()
            ->linkToRoute("point_purchase_cancel", fn (Purchase $purchase) => ["id" => $purchase->getId()]);

        $refuse = Action::new("refuse", "Refuser")
            ->addCssClass("text-danger")
            ->displayIf(fn (Purchase $purchase) => $purchaseStateMachine->can($purchase, "refuse"))
            ->displayAsLink()
            ->linkToRoute("point_purchase_refuse", fn (Purchase $purchase) => ["id" => $purchase->getId()]);

        $accept = Action::new("accept", "Accepter")
            ->addCssClass("text-success")
            ->displayIf(fn (Purchase $purchase) => $purchaseStateMachine->can($purchase, "accept"))
            ->displayAsLink()
            ->linkToRoute("point_purchase_accept", fn (Purchase $purchase) => ["id" => $purchase->getId()]);

        return $actions
            ->add(Crud::PAGE_INDEX, $cancel)
            ->add(Crud::PAGE_DETAIL, $cancel)
            ->add(Crud::PAGE_INDEX, $refuse)
            ->add(Crud::PAGE_DETAIL, $refuse)
            ->add(Crud::PAGE_INDEX, $accept)
            ->add(Crud::PAGE_DETAIL, $accept)
            ->update(Crud::PAGE_DETAIL, Action::DELETE, $actionCallback)
            ->update(Crud::PAGE_INDEX, Action::DELETE, $actionCallback)
            ->update(Crud::PAGE_DETAIL, Action::EDIT, $actionCallback)
            ->update(Crud::PAGE_INDEX, Action::EDIT, $actionCallback)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new("account", "Compte point"))
            ->add(DateTimeFilter::new("createdAt", "Date de création"));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Référence')->onlyOnDetail();
        yield AssociationField::new("account", "Compte point")
            ->setFormTypeOption("choice_label", fn (Account $account) => $account->__toString())
            ->setCrudController(AccountCrudController::class);
        yield AssociationField::new("wallet", "Portefeuille")
            ->setCrudController(WalletCrudController::class)
            ->hideOnForm();
        yield DateTimeField::new('createdAt', 'Date')->hideOnForm();
        yield IntegerField::new('points', 'Points');
        yield ChoiceField::new('mode', 'Mode de paiement')
            ->setChoices([
                Purchase::MODE_CHECK => Purchase::MODE_CHECK,
                Purchase::MODE_BANK_WIRE => Purchase::MODE_BANK_WIRE
            ])
            ->hideOnIndex();
        yield TextField::new('internReference', 'Référence interne')->hideOnIndex();
        yield PurchaseStateField::new('state', 'Statut')->hideOnForm();
    }

    /**
     * @Route("/admin/point/purchases/{id}/cancel", name="point_purchase_cancel")
     */
    public function cancel(Purchase $purchase, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $this->purchaseStateMachine->apply($purchase, "cancel");
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash("success", "Achat de points annulé avec succès.");
        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($purchase->getId())
                ->generateUrl()
        );
    }

    /**
     * @Route("/admin/point/purchases/{id}/refuse", name="point_purchase_refuse")
     */
    public function refuse(Purchase $purchase, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $this->purchaseStateMachine->apply($purchase, "refuse");
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash("success", "Achat de points refusé avec succès.");
        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($purchase->getId())
                ->generateUrl()
        );
    }

    /**
     * @Route("/admin/point/purchases/{id}/accept", name="point_purchase_accept")
     */
    public function accept(Purchase $purchase, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $this->purchaseStateMachine->apply($purchase, "accept");
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash("success", "Achat de points accepté avec succès, le compte a été crédité avec succès.");
        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($purchase->getId())
                ->generateUrl()
        );
    }
}
