<?php

namespace App\Controller\Admin;

use App\Entity\Disciplina;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class DisciplinaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Disciplina::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Disciplina')
            ->setEntityLabelInPlural('Disciplinas')
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de Disciplinas')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Detalle de Disciplina');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield Field::new('nombre');
        yield Field::new('descripcion');
        yield Field::new('borrado');
        yield Field::new('activa');
        yield Field::new('fechaCreacion')->hideOnForm();
        yield Field::new('fechaModificacion')->hideOnForm();
        yield Field::new('orderSort');
    }
}
