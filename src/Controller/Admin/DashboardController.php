<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Posgrado\ResidenciaExImssCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Fofoe Migracion');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()->disable(Action::DELETE);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield from $this->agreementMenu();
        yield from $this->posgradoMenu();
        // yield MenuItem::linkTo(SomeCrudController::class, 'The Label', 'fas fa-list');
    }

    private function agreementMenu(): iterable
    {
        yield MenuItem::subMenu('Gestión Convenios', 'fa fa-handshake')->setSubItems([
            MenuItem::linkToRoute('Convenios CAME', null, 'admin.convenioscame.convenioscame.index'),
            MenuItem::linkToRoute('Convenios Admin', null, 'admin.conveniosadmin.conveniosadmin.index'),
            MenuItem::linkTo(AgreementSuperCrudController::class, 'Convenio - Super Usuario'),
            MenuItem::linkTo(InstitucionSuperCrudController::class, 'Institución - Super Usuario'),
            MenuItem::linkTo(DisciplinaCrudController::class, 'Disciplinas'),
        ]);
    }

    private function posgradoMenu(): iterable
    {
        yield MenuItem::subMenu('Posgrado', 'fa fa-graduation-cap')->setSubItems([
            MenuItem::linkTo(ResidenciaExImssCrudController::class, 'Residencias Ex IMSS'),
        ]);
    }
}
