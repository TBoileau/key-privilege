<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Administrator;
use App\Entity\Company\Client;
use App\Entity\Company\Member;
use App\Entity\Company\Organization;
use App\Entity\Key\Account;
use App\Entity\Key\Purchase;
use App\Entity\Key\Transaction;
use App\Entity\Key\Transfer;
use App\Entity\Key\Wallet;
use App\Entity\Order\Order;
use App\Entity\Question;
use App\Entity\Rules;
use App\Entity\User\Collaborator;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Entity\User\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Key Privilege');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fas fa-home');
        yield MenuItem::linkToLogout('Se déconnecter', 'fa fa-sign-out');
        yield MenuItem::linkToCrud('Administrateurs', 'fa fa-user-shield', Administrator::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Règlements', 'fa fa-file', Rules::class);
        yield MenuItem::linkToCrud('FAQ', 'fa fa-question-circle', Question::class);
        yield MenuItem::section('Adhérents');
        yield MenuItem::linkToCrud('Groupements', 'fa fa-building', Organization::class);
        yield MenuItem::linkToCrud('Adhérents', 'fa fa-building', Member::class);
        yield MenuItem::linkToCrud('Administrateur', 'fa fa-users', Manager::class);
        yield MenuItem::linkToCrud('Commerciaux', 'fa fa-users', SalesPerson::class);
        yield MenuItem::linkToCrud('Collaborateurs', 'fa fa-users', Collaborator::class);
        yield MenuItem::section('Clients');
        yield MenuItem::linkToCrud('Sociétés', 'fa fa-building', Client::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', Customer::class);
        yield MenuItem::section('Gestion des points');
        yield MenuItem::linkToCrud('Comptes points', 'fa fa-balance-scale', Account::class);
        yield MenuItem::linkToCrud('Achats de points', 'fa fa-bell', Purchase::class);
        yield MenuItem::linkToCrud('Portefeuilles', 'fa fa-bank', Wallet::class);
        yield MenuItem::linkToCrud('Transactions', 'fa fa-list', Transaction::class);
        yield MenuItem::linkToCrud('Transferts', 'fa fa-exchange', Transfer::class);
        yield MenuItem::section('Commandes');
        yield MenuItem::linkToCrud('Commandes', 'fa fa-bell', Order::class);
    }
}
