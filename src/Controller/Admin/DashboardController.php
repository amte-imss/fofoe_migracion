<?php

namespace App\Controller\Admin;

use App\Controller\Admin\CamposClinicos\CampoClinicoCrudController;
use App\Controller\Admin\CamposClinicos\CarreraCrudController;
use App\Controller\Admin\CamposClinicos\UserCameCrudController;
use App\Controller\Admin\CamposClinicos\UserJdesCrudController;
use App\Controller\Admin\Posgrado\CargaMasivaCrudController;
use App\Controller\Admin\Posgrado\ResidenciaExImssCrudController;
use App\Controller\Admin\Posgrado\ResidenciaExNoImssCrudController;
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
        if ($this->isGranted('ROLE_EDU_PER') || $this->isGranted('ROLE_SUPER')) {
            yield MenuItem::linkToRoute('Educación Permanente', 'fa fa-book', 'edu-per.index');
        }
        yield from $this->agreementMenu();
        yield from $this->fofoeMenu();
        yield from $this->fofoeFilesMenu();
        yield from $this->usersMenu();
        yield from $this->globalConfigMenu();
        yield from $this->posgradoMenu();
        yield from $this->camposClinicosMenu();
    }

    private function agreementMenu(): iterable
    {
        yield MenuItem::subMenu('Gestión Convenios', 'fa fa-handshake')->setSubItems([
            MenuItem::linkToRoute('Convenios CAME', null, 'admin.convenioscame.convenioscame.index'),
            MenuItem::linkToRoute('Convenios Admin', null, 'admin.conveniosadmin.conveniosadmin.index'),
            MenuItem::linkTo(AgreementSuperCrudController::class, 'Convenio - Super Usuario'),
            MenuItem::linkTo(InstitucionSuperCrudController::class, 'Institución - Super Usuario'),
            MenuItem::linkTo(DisciplinaCrudController::class, 'Disciplinas'),
            MenuItem::linkTo(ConvenioEliminadoCrudController::class, 'Convenios Eliminados'),
            MenuItem::linkTo(VencidosCrudController::class, 'Convenios Vencidos por cargar'),
        ]);
    }

    private function posgradoMenu(): iterable
    {
        yield MenuItem::subMenu('Posgrado', 'fa fa-graduation-cap')->setSubItems([
            MenuItem::linkTo(ResidenciaExImssCrudController::class, 'Residencias Ex IMSS'),
            MenuItem::linkTo(CargaMasivaCrudController::class, 'Cargas Residentes IMSS'),
            MenuItem::linkTo(ResidenciaExNoImssCrudController::class, 'Residencias Ex NO IMSS'),


        ]);
    }

    private function fofoeMenu(): iterable
    {
        yield MenuItem::subMenu('Gestión FOFOE', 'fa fa-money-bill')->setSubItems([
            MenuItem::linkTo(SuperPagoCrudController::class, 'Pagos'),
            MenuItem::linkTo(SuperResidenciaCrudController::class, 'Residencias'),
            MenuItem::linkTo(SuperEnfermeriaCrudController::class, 'Solicitudes de Enfermería'),
            MenuItem::linkTo(SuperSolicitudCrudController::class, 'Solicitudes CC'),
            MenuItem::linkTo(SuperAlumnoEnfermeriaCrudController::class, 'Alumnos de Enfermería'),
        ]);
    }

    private function fofoeFilesMenu(): iterable
    {
        yield MenuItem::subMenu('Gestión Archivos FOFOE')->setSubItems([
            MenuItem::linkToRoute('Archivos de Pagos', null, 'admin_files_pagos'),
            MenuItem::linkToRoute('Archivos de Solicitud de Ciclos Educativos', null, 'admin_files_solicitud_ce'),
        ]);
    }

    private function usersMenu(): iterable
    {
        yield MenuItem::subMenu('Gestión de Usuarios', 'fa fa-users')->setSubItems([
            MenuItem::linkTo(UserCrudController::class, 'Usuarios'),
        ]);
    }

    private function globalConfigMenu(): iterable
    {
        yield MenuItem::subMenu('Configuración Global', 'fa fa-cogs')->setSubItems([
            MenuItem::linkTo(ConfiguracionGlobalCrudController::class, 'Parámetros Globales'),
            MenuItem::linkTo(PermisoCrudController::class, 'Permisos'),
            MenuItem::linkTo(RolCrudController::class, 'Roles'),

        ]);
    }

    private function camposClinicosMenu(): iterable
    {
        yield MenuItem::subMenu('Gestión Campos Clínicos', 'fa fa-hospital')->setSubItems([
            MenuItem::linkTo(UserCameCrudController::class, 'CAME'),
            MenuItem::linkTo(UserJdesCrudController::class, 'JDES'),
            MenuItem::linkTo(CampoClinicoCrudController::class, 'Campos Clínicos'),
            MenuItem::linkTo(CarreraCrudController::class, 'Carrera'),

        ]);
    }
}
