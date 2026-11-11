<?php

namespace App\Controller\Admin;

use App\Entity\Enfermeria\Alumno;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class SuperAlumnoEnfermeriaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Alumno::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Alumno de Enfermería')
            ->setEntityLabelInPlural('Alumnos de Enfermería')
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de alumnos')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(Alumno $alumno): string => sprintf('Alumno #%d', $alumno->getId()))
            ->setPageTitle(Crud::PAGE_EDIT, fn(Alumno $alumno): string => sprintf('Editar Alumno #%d', $alumno->getId()))
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
        yield FormField::addFieldset('Datos principales', 'fa fa-info-circle');
        yield IdField::new('id', 'ID')->hideOnForm();
        yield Field::new('nombre', 'Nombre');
        yield Field::new('email', 'Email');
        yield Field::new('curp', 'CURP');
        yield Field::new('tipo', 'Tipo');
        yield Field::new('monto', 'Monto');
        yield Field::new('status', 'Status')
            ->setRequired(false)
            ->setFormTypeOption('empty_data', '');
    }
}
