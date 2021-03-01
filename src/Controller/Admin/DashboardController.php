<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Administrator;
use App\Entity\Company\Member;
use App\Entity\Company\Organization;
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
        yield MenuItem::linkToCrud('Groupements', 'fa fa-building', Organization::class);
        yield MenuItem::linkToCrud('Adhérent', 'fa fa-building', Member::class);
    }
}
