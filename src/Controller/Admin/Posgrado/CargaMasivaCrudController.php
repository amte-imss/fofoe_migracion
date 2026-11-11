<?php

namespace App\Controller\Admin\Posgrado;

use App\Entity\Posgrado\CargaMasiva;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADM_FOFOE')]
class CargaMasivaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CargaMasiva::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Carga Residentes IMSS')
            ->setEntityLabelInPlural('Cargas Residentes IMSS')
            ->setPageTitle(Crud::PAGE_INDEX, 'Carga Masiva');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield DateTimeField::new('created_at', 'Fecha de la carga')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
        yield AssociationField::new('usuario', 'Usuario')->hideOnForm();
        yield BooleanField::new('is_delete', 'Eliminado')
            ->renderAsSwitch(false)
            ->onlyOnIndex();
        yield BooleanField::new('is_delete', 'Eliminar')->onlyOnForms();
    }
}
