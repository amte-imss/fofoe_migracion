<?php

namespace App\Controller\Admin;

use App\Entity\Enfermeria\Solicitud as EnfermeriaSolicitud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class SuperEnfermeriaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EnfermeriaSolicitud::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Solicitud de Enfermería')
            ->setEntityLabelInPlural('Solicitudes de Enfermería')
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de solicitudes')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(EnfermeriaSolicitud $solicitud): string => sprintf('Solicitud #%d', $solicitud->getId()))
            ->setPageTitle(Crud::PAGE_EDIT, fn(EnfermeriaSolicitud $solicitud): string => sprintf('Editar solicitud #%d', $solicitud->getId()))
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_NEW], true)
            ? $this->formFields()
            : $this->displayFields($pageName);
    }

    private function displayFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID');
        yield Field::new('periodo', 'Periodo');
        yield TextField::new('periodoFormatted', 'Periodo (legible)')->setSortable(false);
        yield DateField::new('fechaInicio', 'Fecha Inicio')->setFormat('dd/MM/yyyy');
        yield DateField::new('fechaFin', 'Fecha Fin')->setFormat('dd/MM/yyyy');
        yield AssociationField::new('unidad', 'Unidad');

        if (Crud::PAGE_DETAIL === $pageName) {
            yield AssociationField::new('alumnos', 'Alumnos');
        }

        yield DateTimeField::new('createdAt', 'Creado')->setFormat('dd/MM/yyyy HH:mm');
        yield BooleanField::new('isTest', 'Es Test')->renderAsSwitch(false);
    }

    private function formFields(): iterable
    {
        yield FormField::addFieldset('Datos principales', 'fa fa-info-circle');
        yield Field::new('periodo', 'Periodo')
            ->setHelp('Ej.: cuatri, semestre, semestral, anual');
        yield DateField::new('fechaInicio', 'Fecha Inicio')->renderAsNativeWidget();
        yield DateField::new('fechaFin', 'Fecha Fin')->renderAsNativeWidget();

        yield FormField::addFieldset('Relaciones', 'fa fa-link');
        yield AssociationField::new('unidad', 'Unidad');

        yield FormField::addFieldset('Metadatos', 'fa fa-clock');
        yield DateTimeField::new('createdAt', 'Creado')
            ->renderAsNativeWidget()
            ->setDisabled();
        yield BooleanField::new('isTest', 'Es Test');
    }
}
