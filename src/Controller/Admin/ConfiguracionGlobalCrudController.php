<?php

namespace App\Controller\Admin;

use App\Entity\ConfiguracionGlobal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADM_FOFOE')]
class ConfiguracionGlobalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConfiguracionGlobal::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Parámetro Global')
            ->setEntityLabelInPlural('Parámetros Globales')
            ->setPageTitle(Crud::PAGE_INDEX, 'Configuración Global');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield Field::new('clave');
        yield Field::new('valor');
        yield Field::new('descripcion', 'Descripción');
        yield BooleanField::new('activo');
    }
}
