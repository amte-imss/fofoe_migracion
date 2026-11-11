<?php

namespace App\Controller\Admin\CamposClinicos;

use App\Entity\NivelAcademico;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADM_FOFOE')]
class NivelAcademicoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NivelAcademico::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Nivel académico')
            ->setEntityLabelInPlural('Nivel académico')
            ->setPageTitle(Crud::PAGE_INDEX, 'Nivel académico');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->update(
            Crud::PAGE_INDEX,
            Action::NEW,
            fn(Action $action): Action => $action->setLabel('Crear nivel académico')
        );
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield Field::new('nombre', 'Nombre');
    }
}
