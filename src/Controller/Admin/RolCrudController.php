<?php

namespace App\Controller\Admin;

use App\Entity\Rol;
use App\Repository\Admin\AdminRepository;
use App\Util\AdminUtil;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class RolCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rol::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Rol')
            ->setEntityLabelInPlural('Roles')
            ->setPageTitle(Crud::PAGE_INDEX, 'Roles');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->update(
            Crud::PAGE_INDEX,
            Action::NEW,
            fn(Action $action): Action => $action->setLabel('Crear rol')
        );
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield Field::new('nombre', 'Nombre');
        yield Field::new('clave', 'Clave');
        yield AssociationField::new('permisos', 'Permisos')
            ->onlyOnForms()
            ->renderAsNativeWidget()
            ->setFormTypeOptions([
                'choice_label' => AdminUtil::getPermissionName(...),
                'query_builder' => AdminRepository::getAll(...),
                'expanded' => true,
                'multiple' => true,
                'by_reference' => false,
            ]);
    }
}
