<?php

namespace App\Controller\Admin;

use App\Entity\Permiso;
use App\Repository\Admin\AdminRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class PermisoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Permiso::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Permiso')
            ->setEntityLabelInPlural('Permisos')
            ->setPageTitle(Crud::PAGE_INDEX, 'Permisos');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->update(
            Crud::PAGE_INDEX,
            Action::NEW,
            fn(Action $action): Action => $action->setLabel('Crear permiso')
        );
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield Field::new('nombre', 'Nombre');
        yield TextField::new('rol.nombre', 'Rol')->onlyOnIndex();
        yield AssociationField::new('rol', 'Rol')
            ->onlyOnForms()
            ->setFormTypeOptions([
                'choice_label' => 'nombre',
                'query_builder' => AdminRepository::getAllRoles(...),
            ]);
        yield Field::new('clave', 'Clave');
    }
}
